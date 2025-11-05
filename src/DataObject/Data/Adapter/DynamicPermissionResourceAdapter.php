<?php
declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 * @license    Pimcore Open Core License (POCL)
 */


namespace FrontendPermissionToolkitBundle\DataObject\Data\Adapter;

use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\DynamicPermissionResource;
use Pimcore\Bundle\StudioBackendBundle\DataObject\Data\DataNormalizerInterface;
use Pimcore\Bundle\StudioBackendBundle\DataObject\Data\Model\FieldContextData;
use Pimcore\Bundle\StudioBackendBundle\DataObject\Data\SetterDataInterface;
use Pimcore\Bundle\StudioBackendBundle\Exception\Api\InvalidArgumentException;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\UserInterface;

/**
 * @internal
 */
final class DynamicPermissionResourceAdapter implements DataNormalizerInterface, SetterDataInterface
{
    public function getDataForSetter(
        Concrete $element,
        Data $fieldDefinition,
        string $key,
        array $data,
        UserInterface $user,
        ?FieldContextData $contextData = null,
        bool $isPatch = false
    ): ?array
    {
        if (!$fieldDefinition instanceof DynamicPermissionResource) {
            return null;
        }

        if (!isset($data[$key])) {
            throw new InvalidArgumentException(sprintf('Missing data for key "%s"', $key));
        }

        $saveData = [];
        foreach ($data[$key] as $permissionData) {
            if (!$this->checkPermissionOption($fieldDefinition, $permissionData['option'])) {
                throw new InvalidArgumentException(
                    sprintf('Invalid permission option "%s"', $permissionData['option'])
                );
            }
            if (!$this->checkPermissionValue($fieldDefinition, $permissionData['permission'])) {
                throw new InvalidArgumentException(
                    sprintf('Invalid permission value "%s"', $permissionData['permission'])
                );
            }

            $saveData[$permissionData['option']] = $permissionData['permission'];
        }

        return $saveData;
    }

    private function checkPermissionOption(DynamicPermissionResource $definition, string $option): bool
    {
        foreach ($definition->getPermissionResources() as $permissionResource) {
            if ($permissionResource['value'] === $option) {
                return true;
            }
        }

        return false;
    }

    private function checkPermissionValue(DynamicPermissionResource $definition, string $value): bool
    {
        foreach ($definition->getPermissionOptions() as $permission) {
            if ($permission['value'] === $value) {
                return true;
            }
        }

        return false;
    }


    public function normalize(mixed $value, Data $fieldDefinition): mixed
    {
        if (!$fieldDefinition instanceof DynamicPermissionResource) {
            return null;
        }

        $values = [];
        foreach ($value as $option => $permission) {
            $values[] = [
                'label' => $this->getLabel($option, $fieldDefinition),
                'option' => $option,
                'permission' => $permission,
            ];
        }

        return $values;
    }

    public function getLabel(string $key, DynamicPermissionResource $fieldDefinition)
    {

        foreach ($fieldDefinition->getPermissionResources() as $permissionResource) {
            if ($permissionResource['value'] === $key) {
                return $permissionResource['label'];
            }
        }

        return $key;
    }

}
