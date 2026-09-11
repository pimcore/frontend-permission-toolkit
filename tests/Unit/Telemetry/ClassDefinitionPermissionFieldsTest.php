<?php
declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace FrontendPermissionToolkitBundle\Tests\Unit\Telemetry;

use Codeception\Test\Unit;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Interfaces\PermissionFieldInterface;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionManyToManyRelation;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionResource;
use FrontendPermissionToolkitBundle\Telemetry\ClassDefinitionPermissionFields;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Data\Block;
use Pimcore\Model\DataObject\ClassDefinition\Data\FieldDefinitionEnrichmentModelInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\Input;
use Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields;
use Pimcore\Model\Exception\NotFoundException;
use Pimcore\Telemetry\Snapshot\SnapshotQueryRunner;
use Symfony\Component\Yaml\Yaml;
use Throwable;

/**
 * The data-model walk over the real {@see SnapshotQueryRunner} on a scripted connection, with the definition
 * loaders and the brick listing injected: what makes the answer true, false, or - when part of the model
 * could not be read and nothing was found elsewhere - null. Definitions are real definition objects with
 * enrichment suppressed, so this runs without a kernel. The class ids always flow through the query runner,
 * so the exclusion of Portal Engine's classes is observed, not assumed.
 */
class ClassDefinitionPermissionFieldsTest extends Unit
{
    private const CLASSES_SQL = 'SELECT id, name FROM classes';

    private const DATABASE_DOWN = 'database down';

    private const FILE_MISSING = 'definition file missing';

    /**
     * The bundle's data-type registration - the single source of which types are permission fields.
     */
    private const REGISTRATION = __DIR__ . '/../../../src/Resources/config/pimcore/config.yml';

    /**
     * @var list<string>
     */
    private array $executedSql = [];

    /**
     * Every permission field type the bundle registers is a hit. The cases come from the registration file
     * itself, so a type added there without the marker interface fails here instead of leaving the metric
     * blind.
     */
    public function testEveryRegisteredPermissionFieldTypeIsAHit(): void
    {
        $registered = $this->registeredPermissionFieldTypes();
        $this->assertNotEmpty($registered);

        foreach ($registered as $type => $class) {
            $field = new $class();
            $this->assertInstanceOf(PermissionFieldInterface::class, $field, $type . ' must carry the marker');
            $this->assertSame($type, $field->getFieldType(), $class . ' must report its registered type');
            $field->setName('permissions');

            $this->assertTrue(
                $this->walk([7 => 'Product'], ['7' => $this->definition($field)])->exist(),
                $type . ' should count as set up'
            );
        }
    }

    public function testAPermissionFieldOnACustomerClassIsFound(): void
    {
        $walk = $this->walk(
            [7 => 'Product', 8 => 'Category'],
            ['7' => $this->definition($this->input('sku')), '8' => $this->definition($this->permissionField())]
        );

        $this->assertTrue($walk->exist());
    }

    public function testAPermissionFieldOnABrickIsFound(): void
    {
        $walk = $this->walk(
            [7 => 'Product'],
            ['7' => $this->definition($this->input('sku'))],
            [
                'Pricing' => $this->definition($this->input('price')),
                'Access' => $this->definition($this->permissionField()),
            ]
        );

        $this->assertTrue($walk->exist());
    }

    /**
     * Portal Engine's reserved classes are that bundle's set-up, not the customer's: their permission fields
     * do not count, and their definitions are not even loaded.
     */
    public function testPortalEngineShippedClassesAreLeftOut(): void
    {
        $walk = $this->walk(
            [3 => 'PortalUser', 9 => 'PortalUserGroup'],
            [
                '3' => $this->definition($this->permissionField()),
                '9' => $this->definition($this->permissionField()),
            ]
        );

        $this->assertFalse($walk->exist());
    }

    /**
     * The exclusion matches the exact reserved names; a customer's own `PortalUserProfile` still counts.
     */
    public function testAClassNamedLikeAReservedOneStillCounts(): void
    {
        $walk = $this->walk([12 => 'PortalUserProfile'], ['12' => $this->definition($this->permissionField())]);

        $this->assertTrue($walk->exist());
    }

    /**
     * Everything readable, nothing found: a definite "not set up", not unknown.
     */
    public function testAModelWithoutAPermissionFieldIsNotSetUpRatherThanUnknown(): void
    {
        $walk = $this->walk(
            [7 => 'Product'],
            ['7' => $this->definition($this->input('sku'), $this->input('title'))],
            ['Pricing' => $this->definition($this->input('price'))]
        );

        $this->assertFalse($walk->exist());
    }

    public function testAnEmptyModelIsNotSetUp(): void
    {
        $this->assertFalse($this->walk([], [])->exist());
    }

    /**
     * The toolkit resolves permissions from a class's and a brick's top-level fields only; a permission field
     * inside localized fields or a block is never in effect, so it does not count as set up either.
     */
    public function testAPermissionFieldNestedInAContainerIsNotCounted(): void
    {
        $localized = new Localizedfields();
        $localized->setName('localizedfields');
        $localized->setChildren([$this->permissionField()]);
        $block = new Block();
        $block->setName('content');
        $block->setChildren([$this->permissionField()]);

        $this->assertFalse($this->walk([7 => 'Product'], ['7' => $this->definition($localized, $block)])->exist());
    }

    /**
     * The marker interface is the criterion, not the type name: a foreign type that merely reports one of our
     * names is not one of ours.
     */
    public function testAForeignTypeWithAFamiliarNameIsNotAHit(): void
    {
        $lookalike = $this->createMock(Data::class);
        $lookalike->method('getFieldType')->willReturn('permissionResource');

        $this->assertFalse($this->walk([7 => 'Product'], ['7' => $this->definition($lookalike)])->exist());
    }

    public function testTheClassIdsAreReadFromTheClassesTable(): void
    {
        $this->walk([7 => 'Product'], ['7' => $this->definition($this->input('sku'))])->exist();

        $this->assertSame([self::CLASSES_SQL], $this->executedSql);
    }

    /**
     * The class table could not be read: unknown, not "no classes" - unless a brick holds a field.
     */
    public function testAFailingClassQueryWithNothingFoundElsewhereIsUnknown(): void
    {
        $this->assertNull($this->walk(new ConnectionException(self::DATABASE_DOWN), [])->exist());
    }

    public function testAPermissionFieldOnABrickAfterAFailingClassQueryStillCounts(): void
    {
        $walk = $this->walk(
            new ConnectionException(self::DATABASE_DOWN),
            [],
            ['Access' => $this->definition($this->permissionField())]
        );

        $this->assertTrue($walk->exist());
    }

    /**
     * A definition whose file throws while loading is unreadable, not the end of the listing: the definition
     * after it is still loaded and scanned, and a field found there wins.
     */
    public function testAPermissionFieldAfterADefinitionWhoseLoadThrowsStillCounts(): void
    {
        $walk = $this->walk(
            [7 => 'Product', 8 => 'Category'],
            [
                '7' => new NotFoundException(self::FILE_MISSING),
                '8' => $this->definition($this->permissionField()),
            ]
        );

        $this->assertTrue($walk->exist());
    }

    public function testADefinitionWhoseLoadThrowsWithNothingFoundElsewhereIsUnknown(): void
    {
        $walk = $this->walk(
            [7 => 'Product', 8 => 'Category'],
            ['7' => new NotFoundException(self::FILE_MISSING), '8' => $this->definition($this->input('title'))]
        );

        $this->assertNull($walk->exist());
    }

    /**
     * The same holds for a brick whose definition throws while loading.
     */
    public function testAPermissionFieldAfterABrickWhoseLoadThrowsStillCounts(): void
    {
        $walk = $this->walk(
            [],
            [],
            [
                'Broken' => new NotFoundException('brick file missing'),
                'Access' => $this->definition($this->permissionField()),
            ]
        );

        $this->assertTrue($walk->exist());
    }

    /**
     * A load that yields nothing - null for a missing definition, false from a file that did not return one -
     * is unreadable, not a crash and not "no field".
     */
    public function testADefinitionThatFailsToLoadIsUnknown(): void
    {
        $this->assertNull($this->walk([7 => 'Product'], ['7' => null])->exist());
        $this->assertNull($this->walk([7 => 'Product'], ['7' => false])->exist());
    }

    /**
     * The failure may sit inside the definition, when its fields are read: still unknown rather than an
     * exception escaping to the collector.
     */
    public function testAnUnreadableDefinitionIsUnknown(): void
    {
        $this->assertNull($this->walk([7 => 'Product'], ['7' => $this->unreadableDefinition()])->exist());
    }

    /**
     * An entry that is not a field definition at all is a corrupted definition, not an empty one: unknown
     * unless another definition holds a permission field.
     */
    public function testACorruptedEntryIsUnknownUnlessAnotherDefinitionHoldsAPermissionField(): void
    {
        $corrupted = $this->createMock(FieldDefinitionEnrichmentModelInterface::class);
        $corrupted->method('getFieldDefinitions')->willReturn(['not a definition', $this->input('title')]);

        $this->assertNull($this->walk([7 => 'Product'], ['7' => $corrupted])->exist());
        $this->assertTrue($this->walk(
            [7 => 'Product', 8 => 'Category'],
            ['7' => $corrupted, '8' => $this->definition($this->permissionField())]
        )->exist());
    }

    /**
     * The brick listing may fail midway, after yielding some keys. Nothing found among those: unknown.
     */
    public function testABrickListingThatFailsMidwayMakesTheAnswerUnknown(): void
    {
        $walk = $this->walkWithBrickListing(
            function (): iterable {
                yield 'Pricing';

                throw new ConnectionException(self::DATABASE_DOWN);
            },
            ['Pricing' => $this->definition($this->input('price'))]
        );

        $this->assertNull($walk->exist());
    }

    public function testAPermissionFieldYieldedBeforeTheBrickListingFailsStillCounts(): void
    {
        $walk = $this->walkWithBrickListing(
            function (): iterable {
                yield 'Access';

                throw new ConnectionException(self::DATABASE_DOWN);
            },
            ['Access' => $this->definition($this->permissionField())]
        );

        $this->assertTrue($walk->exist());
    }

    /**
     * @param array<int, string>|Throwable $classes the `classes` table as id => name, or the failure reading it
     * @param array<int|string, mixed> $definitionsById what loading each class id yields; a Throwable is thrown
     * @param array<int|string, mixed> $bricksByKey what loading each brick key yields; the keys are the listing
     */
    private function walk(
        array|Throwable $classes,
        array $definitionsById,
        array $bricksByKey = []
    ): ClassDefinitionPermissionFields {
        return $this->walkWithBrickListing(
            static fn (): iterable => array_keys($bricksByKey),
            $bricksByKey,
            $classes,
            $definitionsById
        );
    }

    /**
     * @param callable(): iterable<int|string> $brickNames
     * @param array<int|string, mixed> $bricksByKey
     * @param array<int, string>|Throwable $classes
     * @param array<int|string, mixed> $definitionsById
     */
    private function walkWithBrickListing(
        callable $brickNames,
        array $bricksByKey,
        array|Throwable $classes = [],
        array $definitionsById = []
    ): ClassDefinitionPermissionFields {
        $this->executedSql = [];

        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAllKeyValue')->willReturnCallback(
            function (string $sql) use ($classes): array {
                $this->executedSql[] = $sql;

                if ($classes instanceof Throwable) {
                    throw $classes;
                }

                return $classes;
            }
        );

        return new ClassDefinitionPermissionFields(
            new SnapshotQueryRunner($connection, 0),
            static fn (string $id): mixed => self::load($definitionsById, $id),
            $brickNames(...),
            static fn (string $key): mixed => self::load($bricksByKey, $key)
        );
    }

    /**
     * @param array<int|string, mixed> $byKey
     */
    private static function load(array $byKey, string $key): mixed
    {
        $result = $byKey[$key] ?? null;

        if ($result instanceof Throwable) {
            throw $result;
        }

        return $result;
    }

    /**
     * @return array<string, class-string<Data>> field type => data class, as registered with core
     */
    private function registeredPermissionFieldTypes(): array
    {
        $config = Yaml::parseFile(self::REGISTRATION);

        return $config['pimcore']['objects']['class_definitions']['data']['map'];
    }

    /**
     * A definition holding the given fields; a block stands in for a class or brick, since both expose their
     * fields the same way.
     */
    private function definition(Data ...$fields): Block
    {
        $block = new Block();
        $block->setName('definition');
        $block->setChildren($fields);

        return $block;
    }

    private function unreadableDefinition(): FieldDefinitionEnrichmentModelInterface
    {
        $definition = $this->createMock(FieldDefinitionEnrichmentModelInterface::class);
        $definition->method('getFieldDefinitions')
            ->willThrowException(new NotFoundException(self::FILE_MISSING));

        return $definition;
    }

    private function permissionField(): PermissionResource
    {
        $field = new PermissionResource();
        $field->setName('permissions');

        return $field;
    }

    private function input(string $name): Input
    {
        $input = new Input();
        $input->setName($name);

        return $input;
    }
}
