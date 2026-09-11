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

namespace FrontendPermissionToolkitBundle\Telemetry;

use Closure;
use Exception;
use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Attribute\PermissionField;
use FrontendPermissionToolkitBundle\Service;
use function in_array;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Data\FieldDefinitionEnrichmentModelInterface as FieldContainer;
use Pimcore\Model\DataObject\Objectbrick\Definition as ObjectbrickDefinition;
use Pimcore\Model\DataObject\Objectbrick\Definition\Listing as ObjectbrickListing;
use Pimcore\Telemetry\Snapshot\SnapshotQueryRunner;
use ReflectionClass;

/**
 * Walks the data model - every class definition of the customer's own and every object brick - for a field
 * whose type carries the {@see PermissionField} attribute, one of the toolkit's permission field types,
 * stopping at the first hit.
 *
 * Only the placements the toolkit resolves count: its {@see Service} reads a user object's class fields and
 * the fields of the bricks on it, top level each, and never descends into localized fields, blocks or field
 * collections. A permission field nested there, or on a field collection, would not be in effect, so this
 * walk looks at the same top-level fields and nothing else.
 *
 * Portal Engine's `PortalUser` and `PortalUserGroup` are left out while that bundle is registered in the
 * kernel: its installer ships them with permission fields already on them and reserves both names, so their
 * fields are Portal Engine's set-up, not the customer's - without the exclusion every Portal Engine install
 * would read as having set the toolkit up. The toolkit installs on its own as well, and there a customer's own
 * class may carry either name, so the exclusion applies only when Portal Engine is actually present.
 *
 * Raw definitions only (`suppressEnrichment`): the walk needs no container-bound enrichment and no user,
 * which matters because the snapshot runs in the CLI maintenance context.
 *
 * Unknown is not the same as absent: when the class table, the brick listing or a definition could not be
 * read - or a definition holds an entry that is not a field definition at all - and nothing was found
 * elsewhere, the answer is null. A field that was found is a definite yes regardless. Only `Exception` is
 * caught - a programming error surfaces to the collector, as it should.
 *
 * Every definition is loaded on its own. Class ids come from core's time-boxed telemetry query runner, which
 * throws when the query fails - unlike the class listing, which silently drops a class whose definition
 * fails to load and answers 0 from a failed count. Bricks are loaded by key. Every load is guarded on its
 * own: a definition that fails - by returning null or by throwing from its file - yields an unreadable marker
 * instead of hiding its siblings. The loaders and the brick listing default to the model's own; tests
 * inject theirs, which is what keeps the walk testable without a kernel while the class ids still flow
 * through the query runner.
 *
 * @phpstan-type Definition ClassDefinition|ObjectbrickDefinition|FieldContainer
 *
 * @internal
 */
final readonly class ClassDefinitionPermissionFields implements PermissionFieldsInterface
{
    /**
     * Classes Portal Engine ships with permission fields already on them - its users and groups, whose
     * names its installer reserves.
     *
     * @var list<string>
     */
    private const PORTAL_ENGINE_CLASSES = ['PortalUser', 'PortalUserGroup'];

    /**
     * The bundle name Portal Engine registers with the kernel.
     */
    private const PORTAL_ENGINE_BUNDLE = 'PimcorePortalEngineBundle';

    /**
     * @param array<string, string> $bundles the kernel's registered bundles, name => class (`%kernel.bundles%`)
     * @param Closure(string): mixed|null $loadClass loads one class definition by id; anything that is not
     *                                              a {@see Definition} is unreadable
     * @param Closure(): iterable<int|string>|null $brickNames lists the object brick keys
     * @param Closure(string): mixed|null $loadBrick loads one brick definition by key
     */
    public function __construct(
        private SnapshotQueryRunner $queries,
        private array $bundles,
        private ?Closure $loadClass = null,
        private ?Closure $brickNames = null,
        private ?Closure $loadBrick = null,
    ) {
    }

    public function exist(): ?bool
    {
        $classes = $this->scan(
            fn (): iterable => $this->customerClassIds(),
            $this->loadClass ?? static fn (string $id): ?ClassDefinition => ClassDefinition::getById($id)
        );

        if ($classes === true) {
            return true;
        }

        $bricks = $this->scan(
            $this->brickNames ?? static fn (): iterable => (new ObjectbrickListing())->loadNames(),
            $this->loadBrick ?? static fn (string $key): ?ObjectbrickDefinition => ObjectbrickDefinition::getByKey($key)
        );

        if ($bricks === true) {
            return true;
        }

        // Nothing found. That is a definite "not set up" only if both sources could be read completely;
        // an unreadable part might hold a field, so the answer is then unknown rather than unused.
        return $classes === false && $bricks === false ? false : null;
    }

    /**
     * The class ids to walk: every class, minus the ones Portal Engine ships with permission fields - but only
     * while Portal Engine is registered; without it, a class of either name is the customer's own.
     *
     * @return list<string>
     */
    private function customerClassIds(): array
    {
        $portalEngineActive = isset($this->bundles[self::PORTAL_ENGINE_BUNDLE]);
        $ids = [];

        foreach ($this->queries->fetchAllKeyValue('SELECT id, name FROM classes') as $id => $name) {
            if ($portalEngineActive && in_array($name, self::PORTAL_ENGINE_CLASSES, true)) {
                continue;
            }

            $ids[] = (string) $id;
        }

        return $ids;
    }

    /**
     * Loads and scans one definition per key, guarding each load on its own: a definition whose file throws
     * yields the unreadable marker instead of ending the listing, so the definitions after it are still
     * scanned and a later hit still wins.
     *
     * @param Closure(): iterable<int|string> $keys
     * @param Closure(string): mixed $load
     */
    private function scan(Closure $keys, Closure $load): ?bool
    {
        $unreadable = false;

        try {
            foreach ($keys() as $key) {
                try {
                    $definition = $load((string) $key);
                } catch (Exception) {
                    $definition = null;
                }

                $found = $this->scanDefinition($definition);

                if ($found === true) {
                    return true;
                }

                $unreadable = $unreadable || $found === null;
            }
        } catch (Exception) {
            // The listing itself failed; the other source may still hold a field.
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
        } catch (Exception) {
            // This definition could not be read; a later hit still wins.
            return null;
        }

        return $this->containsPermissionField($fields);
    }

    /**
     * Top-level fields only - the placements the toolkit resolves; see the class docblock. A permission field is
     * recognised by the {@see PermissionField} attribute every permission data type the bundle registers carries,
     * so there is no list of type names to keep in step with the registration.
     *
     * @param iterable<mixed> $fields
     */
    private function containsPermissionField(iterable $fields): ?bool
    {
        $unreadable = false;

        foreach ($fields as $field) {
            if (!$field instanceof Data) {
                // Not a field definition at all - a corrupted entry; its siblings are still readable.
                $unreadable = true;

                continue;
            }

            if (self::isPermissionField($field)) {
                return true;
            }
        }

        return $unreadable ? null : false;
    }

    /**
     * Whether the field's type - or one it extends - carries the {@see PermissionField} attribute.
     */
    private static function isPermissionField(Data $field): bool
    {
        for ($class = new ReflectionClass($field); $class !== false; $class = $class->getParentClass()) {
            if ($class->getAttributes(PermissionField::class) !== []) {
                return true;
            }
        }

        return false;
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
}
