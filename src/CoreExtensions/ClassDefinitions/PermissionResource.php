<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions;

use FrontendPermissionToolkitBundle\Service;
use Pimcore\Model\DataObject\ClassDefinition\Data\Select;
use Pimcore\Model\DataObject\Concrete;

class PermissionResource extends Select
{
    /**
     * @deprecated Will be removed in frontend-permission-toolkit 3, use getFieldType() instead.
     */
    public string $fieldtype = 'permissionResource';

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

    public function getDataForResource(mixed $data, Concrete $object = null, array $params = []): ?string
    {
        return $this->checkForEmpty($data);
    }

    public function getDataFromResource(mixed $data, Concrete $object = null, array $params = []): ?string
    {
        return $this->checkForEmpty($data);
    }

    public function getDataForQueryResource(mixed $data, Concrete $object = null, array $params = []): ?string
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
