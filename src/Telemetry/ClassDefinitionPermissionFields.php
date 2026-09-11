<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace FrontendPermissionToolkitBundle\Telemetry;

use Exception;
use FrontendPermissionToolkitBundle\Service;
use function in_array;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data\FieldDefinitionEnrichmentModelInterface as FieldContainer;
use Pimcore\Model\DataObject\Objectbrick\Definition as ObjectbrickDefinition;
use Pimcore\Model\DataObject\Objectbrick\Definition\Listing as ObjectbrickListing;
use Pimcore\Telemetry\Snapshot\SnapshotQueryRunner;

/**
 * Walks the data model - every class definition of the customer's own and every object brick - for one of
 * the toolkit's permission field types, stopping at the first hit.
 *
 * Classes and bricks are where the toolkit itself resolves permissions from - its {@see Service} reads a
 * user object's class fields and the bricks on it - so a permission field anywhere else would not be in
 * effect. Portal Engine's `PortalUser` and `PortalUserGroup` are left out: that bundle's installer
 * ships them with permission fields already on them and reserves both names, so their fields are Portal
 * Engine's set-up, not the customer's - without the exclusion every Portal Engine install would read as
 * having set the toolkit up.
 *
 * Raw definitions only (`suppressEnrichment`): the walk needs no container-bound enrichment and no user,
 * which matters because the snapshot runs in the CLI maintenance context.
 *
 * Unknown is not the same as absent: when a listing, a definition or a container inside a definition could
 * not be read and nothing was found elsewhere, the answer is null. A field that was found is a definite yes
 * regardless. Only `Exception` is caught - a programming error surfaces to the collector, as it should.
 *
 * Every definition is loaded on its own. Class ids come from core's time-boxed telemetry query runner, which
 * throws when the query fails - unlike the class listing, which silently drops a class whose definition
 * fails to load and answers 0 from a failed count. Bricks are loaded by key. Every load is guarded on its
 * own: a definition that fails - by returning null or by throwing from its file - yields an unreadable marker
 * instead of hiding its siblings. The listings default to these two; tests inject their own, which is what
 * keeps the walk testable without a kernel.
 *
 * @phpstan-type Definition ClassDefinition|ObjectbrickDefinition|FieldContainer
 *
 * @internal
 */
final readonly class ClassDefinitionPermissionFields implements PermissionFieldsInterface
{
    /**
     * The field types this bundle registers with the class definition.
     *
     * @var list<string>
     */
    public const FIELD_TYPES = [
        'permissionResource',
        'dynamicPermissionResource',
        'permissionManyToManyRelation',
        'permissionManyToOneRelation',
    ];

    /**
     * Classes another bundle ships with permission fields already on them - Portal Engine's users and
     * groups, whose names its installer reserves.
     *
     * @var list<string>
     */
    private const BUNDLE_SHIPPED_CLASSES = ['PortalUser', 'PortalUserGroup'];

    /**
     * @param list<callable(): iterable<mixed>> $listings anything that is not a {@see Definition} is unreadable
     */
    public function __construct(
        private FieldTypeScanner $scanner,
        private SnapshotQueryRunner $queries,
        private array $listings = [],
    ) {
    }

    public function exist(): ?bool
    {
        $unreadable = false;

        foreach ($this->listings() as $listing) {
            $found = $this->scanListing($listing);

            if ($found === true) {
                return true;
            }

            $unreadable = $unreadable || $found === null;
        }

        return $unreadable ? null : false;
    }

    /**
     * The class ids to walk: every class except the ones another bundle ships with permission fields.
     *
     * @param array<int|string, mixed> $idToName the `classes` table as id => name
     *
     * @return list<string>
     */
    public static function customerClassIds(array $idToName): array
    {
        $ids = [];

        foreach ($idToName as $id => $name) {
            if (!in_array($name, self::BUNDLE_SHIPPED_CLASSES, true)) {
                $ids[] = (string) $id;
            }
        }

        return $ids;
    }

    /**
     * @param callable(): iterable<mixed> $listing
     */
    private function scanListing(callable $listing): ?bool
    {
        $unreadable = false;

        try {
            foreach ($listing() as $definition) {
                $found = $this->scanDefinition($definition);

                if ($found === true) {
                    return true;
                }

                $unreadable = $unreadable || $found === null;
            }
        } catch (Exception) {
            // The listing itself failed; the other listings may still hold a field.
            $unreadable = true;
        }

        return $unreadable ? null : false;
    }

    private function scanDefinition(mixed $definition): ?bool
    {
        if (!$this->isDefinition($definition)) {
            // A definition that could not be loaded, or a file that did not yield one.
            return null;
        }

        try {
            $fields = $definition->getFieldDefinitions(['suppressEnrichment' => true]);

            return $this->scanner->containsAnyType($fields, self::FIELD_TYPES);
        } catch (Exception) {
            // This definition could not be read; a later hit still wins.
            return null;
        }
    }

    /**
     * @phpstan-assert-if-true Definition $definition
     */
    private function isDefinition(mixed $definition): bool
    {
        return $definition instanceof ClassDefinition
            || $definition instanceof ObjectbrickDefinition
            || $definition instanceof FieldContainer;
    }

    /**
     * @return list<callable(): iterable<mixed>>
     */
    private function listings(): array
    {
        if ($this->listings !== []) {
            return $this->listings;
        }

        return [
            fn (): iterable => self::loadEach(
                self::customerClassIds($this->queries->fetchAllKeyValue('SELECT id, name FROM classes')),
                static fn (string $id): ?ClassDefinition => ClassDefinition::getById($id)
            ),
            static fn (): iterable => self::loadEach(
                (new ObjectbrickListing())->loadNames(),
                static fn (string $key): ?ObjectbrickDefinition => ObjectbrickDefinition::getByKey($key)
            ),
        ];
    }

    /**
     * Loads one definition per key, guarding each load on its own: a definition whose file throws yields the
     * unreadable marker instead of ending the listing, so the definitions after it are still scanned.
     *
     * @param iterable<int|string> $keys
     * @param callable(string): mixed $load
     *
     * @return iterable<mixed>
     */
    private static function loadEach(iterable $keys, callable $load): iterable
    {
        foreach ($keys as $key) {
            try {
                yield $load((string) $key);
            } catch (Exception) {
                yield null;
            }
        }
    }
}
