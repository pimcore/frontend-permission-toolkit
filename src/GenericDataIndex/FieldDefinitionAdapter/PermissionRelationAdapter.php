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

namespace FrontendPermissionToolkitBundle\GenericDataIndex\FieldDefinitionAdapter;

use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\DefaultSearch\AttributeType;
use Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DefaultSearch\DataObject\FieldDefinitionAdapter\AbstractAdapter;
use Pimcore\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class PermissionRelationAdapter extends AbstractAdapter
{
    public function getIndexMapping(): array
    {
        return [
            'properties' => [
                'object' => [
                    'type' => AttributeType::LONG->value,
                ],
                'asset' => [
                    'type' => AttributeType::LONG->value,
                ],
                'document' => [
                    'type' => AttributeType::LONG->value,
                ],
            ],
        ];
    }

    public function normalize(mixed $value): ?array
    {
        $fieldDefinition = $this->getFieldDefinition();
        if (!$fieldDefinition instanceof NormalizerInterface) {
            return null;
        }

        $returnValue = [
            'object' => [],
            'asset' => [],
            'document' => [],
        ];
        $normalizedValues = $fieldDefinition->normalize($value);

        if (is_array($normalizedValues)) {
            // Mapping For ManyToOne
            if (isset($normalizedValues['type'], $normalizedValues['id'])) {
                $returnValue[$normalizedValues['type']][] = $normalizedValues['id'];
            }

            foreach ($normalizedValues as $normalizedValue) {
                if (isset($normalizedValue['type'], $normalizedValue['id'])) {
                    $returnValue[$normalizedValue['type']][] = $normalizedValue['id'];
                }
            }
        }

        return $returnValue;
    }
}
