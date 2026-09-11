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
use function in_array;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Data\FieldDefinitionEnrichmentModelInterface;

/**
 * Looks for any of a set of field types anywhere in a field-definition tree: at the top level and inside
 * every container that nests further definitions (localized fields, blocks), however deep.
 *
 * Reads the raw definitions with enrichment suppressed - no container, no dynamic options, no user - so it
 * runs in the CLI maintenance context and in a plain unit test alike.
 *
 * Three answers: true as soon as one of the types is found; false when the whole tree was readable and does
 * not hold one; null when a container could not be read - or an entry is not a field definition at all - and
 * nothing was found elsewhere. A failing entry never hides its siblings - the scan goes on, and a later match
 * still wins.
 *
 * @internal
 */
final class FieldTypeScanner
{
    /**
     * @param iterable<mixed> $definitions
     * @param list<string> $fieldTypes
     */
    public function containsAnyType(iterable $definitions, array $fieldTypes): ?bool
    {
        $unreadable = false;

        foreach ($definitions as $definition) {
            if (!$definition instanceof Data) {
                // Not a field definition at all - a corrupted entry; its siblings are still readable.
                $unreadable = true;

                continue;
            }

            if (in_array($definition->getFieldType(), $fieldTypes, true)) {
                return true;
            }

            if (!$definition instanceof FieldDefinitionEnrichmentModelInterface) {
                continue;
            }

            try {
                $children = $definition->getFieldDefinitions(['suppressEnrichment' => true]);
                $nested = $this->containsAnyType($children, $fieldTypes);
            } catch (Exception) {
                // This container could not be read; its siblings still can.
                $nested = null;
            }

            if ($nested === true) {
                return true;
            }

            if ($nested === null) {
                $unreadable = true;
            }
        }

        return $unreadable ? null : false;
    }
}
