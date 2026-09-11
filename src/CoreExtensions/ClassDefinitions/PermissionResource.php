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

namespace FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions;

use FrontendPermissionToolkitBundle\Service;
use Pimcore\Model\DataObject\ClassDefinition\Data\Select;
use Pimcore\Model\DataObject\Concrete;

class PermissionResource extends Select
{
    /**
     * @deprecated Will be removed in frontend-permission-toolkit 4, use getFieldType() instead.
     */
    public string $fieldtype = 'permissionResource';

    public function getFieldType(): string
    {
        return 'permissionResource';
    }

    public function configureOptions()
    {
        $options = [
            ['key' => Service::INHERIT, 'value' => Service::INHERIT],
            ['key' => Service::ALLOW, 'value' => Service::ALLOW],
            ['key' => Service::DENY, 'value' => Service::DENY]
        ];

        $this->setOptions($options);
    }

    protected function checkForEmpty($data)
    {
        if (empty($data)) {
            return Service::INHERIT;
        }

        return $data;
    }

    public function getDataForResource(mixed $data, ?Concrete $object = null, array $params = []): ?string
    {
        return $this->checkForEmpty($data);
    }

    public function getDataFromResource(mixed $data, ?Concrete $object = null, array $params = []): ?string
    {
        return $this->checkForEmpty($data);
    }

    public function getDataForQueryResource(mixed $data, ?Concrete $object = null, array $params = []): ?string
    {
        return $this->checkForEmpty($data);
    }

    public function __wakeup()
    {
        $this->configureOptions();
    }

    public static function __set_state(/* array */ $data): static
    {
        $obj = parent::__set_state($data);
        $obj->configureOptions();

        return $obj;
    }
}
