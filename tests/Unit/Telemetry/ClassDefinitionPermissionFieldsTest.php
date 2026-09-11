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
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\DynamicPermissionResource;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionManyToManyRelation;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionManyToOneRelation;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionResource;
use FrontendPermissionToolkitBundle\Telemetry\ClassDefinitionPermissionFields;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Data\Block;
use Pimcore\Model\DataObject\ClassDefinition\Data\FieldDefinitionEnrichmentModelInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\Input;
use Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields;
use Pimcore\Model\Exception\NotFoundException;
use Pimcore\Telemetry\Snapshot\SnapshotQueryRunner;

/**
 * The listing walk, with the model listings injected: what makes the answer true, false, or - when part of
 * the model could not be read and nothing was found elsewhere - null. Definitions are real definition
 * objects with enrichment suppressed, so this runs without a kernel. A listing may be a generator that
 * yields definitions one by one, as the default listings do, and may fail midway.
 */
class ClassDefinitionPermissionFieldsTest extends Unit
{
    /**
     * The types the walk looks for are exactly the ones the bundle registers - pinned against the real data
     * types, so a renamed or added type cannot silently make the metric blind.
     */
    public function testLooksForExactlyTheTypesTheBundleRegisters(): void
    {
        $registered = [
            (new PermissionResource())->getFieldType(),
            (new DynamicPermissionResource())->getFieldType(),
            (new PermissionManyToManyRelation())->getFieldType(),
            (new PermissionManyToOneRelation())->getFieldType(),
        ];
        $expected = ClassDefinitionPermissionFields::FIELD_TYPES;
        sort($registered);
        sort($expected);

        $this->assertSame($expected, $registered);
    }

    /**
     * Portal Engine's reserved classes are that bundle's set-up, not the customer's; the match is on the exact
     * name, so a customer's own `PortalUserProfile` still counts.
     */
    public function testPortalEngineShippedClassesAreLeftOut(): void
    {
        $classes = [3 => 'PortalUser', 7 => 'Product', 9 => 'PortalUserGroup', 12 => 'PortalUserProfile'];

        $this->assertSame(['7', '12'], ClassDefinitionPermissionFields::customerClassIds($classes));
    }

    public function testAPermissionFieldInTheFirstListingIsFound(): void
    {
        $walk = $this->walk([
            fn (): array => [$this->definition($this->input('sku'), $this->permissionField())],
            fn (): array => [$this->definition($this->input('title'))],
        ]);

        $this->assertTrue($walk->exist());
    }

    public function testAPermissionFieldInALaterListingIsFound(): void
    {
        $walk = $this->walk([
            fn (): array => [],
            fn (): array => [$this->definition($this->input('title'))],
            fn (): array => [$this->definition($this->permissionField())],
        ]);

        $this->assertTrue($walk->exist());
    }

    /**
     * Any of the toolkit's types is a hit, not only the first one on the list.
     */
    public function testAnyOfTheToolkitTypesIsAHit(): void
    {
        $relation = new PermissionManyToManyRelation();
        $relation->setName('groups');

        $this->assertTrue($this->walk([fn (): array => [$this->definition($relation)]])->exist());
    }

    /**
     * Every listing readable, nothing found: a definite "not set up", not unknown.
     */
    public function testAModelWithoutAPermissionFieldIsNotSetUpRatherThanUnknown(): void
    {
        $walk = $this->walk([
            fn (): array => [$this->definition($this->input('sku')), $this->definition($this->input('title'))],
            fn (): array => [],
        ]);

        $this->assertFalse($walk->exist());
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

        $this->assertFalse($this->walk([fn (): array => [$this->definition($localized, $block)]])->exist());
    }

    /**
     * Exact type match: a similarly named type must never be mistaken for one of the toolkit's.
     */
    public function testTheTypesAreMatchedExactly(): void
    {
        $lookalike = $this->createMock(Data::class);
        $lookalike->method('getFieldType')->willReturn('permissionResources');

        $this->assertFalse($this->walk([fn (): array => [$this->definition($lookalike)]])->exist());
    }

    public function testAnUnreadableListingWithNothingFoundElsewhereIsUnknown(): void
    {
        $walk = $this->walk([
            fn (): array => throw $this->databaseDown(),
            fn (): array => [$this->definition($this->input('title'))],
        ]);

        $this->assertNull($walk->exist());
    }

    /**
     * A positive match wins over an earlier failure: the field that was found is real.
     */
    public function testAPermissionFieldAfterAnUnreadableListingStillCounts(): void
    {
        $walk = $this->walk([
            fn (): array => throw $this->databaseDown(),
            fn (): array => [$this->definition($this->permissionField())],
        ]);

        $this->assertTrue($walk->exist());
    }

    public function testAnUnreadableDefinitionWithNothingFoundElsewhereIsUnknown(): void
    {
        $walk = $this->walk([
            fn (): array => [$this->unreadableDefinition(), $this->definition($this->input('title'))],
        ]);

        $this->assertNull($walk->exist());
    }

    /**
     * An entry that is not a field definition at all is a corrupted definition, not an empty one: unknown
     * unless a readable definition holds a permission field.
     */
    public function testACorruptedEntryIsUnknownUnlessAnotherDefinitionHoldsAPermissionField(): void
    {
        $corrupted = $this->createMock(FieldDefinitionEnrichmentModelInterface::class);
        $corrupted->method('getFieldDefinitions')->willReturn(['not a definition', $this->input('title')]);

        $this->assertNull($this->walk([fn (): array => [$corrupted]])->exist());
        $this->assertTrue($this->walk([
            fn (): array => [$corrupted, $this->definition($this->permissionField())],
        ])->exist());
    }

    /**
     * A listing may fail midway, after yielding some definitions. Nothing found among those: unknown.
     */
    public function testAListingThatFailsMidwayMakesTheAnswerUnknown(): void
    {
        $walk = $this->walk([
            function (): iterable {
                yield $this->definition($this->input('title'));

                throw $this->databaseDown();
            },
        ]);

        $this->assertNull($walk->exist());
    }

    public function testAPermissionFieldYieldedBeforeAListingFailsStillCounts(): void
    {
        $walk = $this->walk([
            function (): iterable {
                yield $this->definition($this->permissionField());

                throw $this->databaseDown();
            },
        ]);

        $this->assertTrue($walk->exist());
    }

    /**
     * A definition that failed to load yields an unreadable marker, as the default listings do; the field in
     * the next definition of the same listing is still found.
     */
    public function testAPermissionFieldAfterAFailedDefinitionFileInTheSameListingStillCounts(): void
    {
        $walk = $this->walk([
            fn (): array => [null, $this->definition($this->permissionField())],
        ]);

        $this->assertTrue($walk->exist());
    }

    /**
     * Anything that is not a definition object is unreadable, not a crash.
     */
    public function testADefinitionFileThatYieldsNoDefinitionIsUnknown(): void
    {
        $walk = $this->walk([
            fn (): array => [false, $this->definition($this->input('title'))],
        ]);

        $this->assertNull($walk->exist());
    }

    /**
     * @param list<callable(): iterable<mixed>> $listings
     */
    private function walk(array $listings): ClassDefinitionPermissionFields
    {
        // The runner is only reached by the default listings, which these tests replace.
        return new ClassDefinitionPermissionFields(
            new SnapshotQueryRunner($this->createMock(Connection::class), 0),
            $listings
        );
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

    private function databaseDown(): ConnectionException
    {
        return new ConnectionException('database down');
    }

    private function unreadableDefinition(): FieldDefinitionEnrichmentModelInterface
    {
        $definition = $this->createMock(FieldDefinitionEnrichmentModelInterface::class);
        $definition->method('getFieldDefinitions')
            ->willThrowException(new NotFoundException('definition file missing'));

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
