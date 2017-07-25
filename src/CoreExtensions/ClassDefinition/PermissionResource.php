<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions;

class PermissionResource extends \Object_Class_Data_Select {

    /**
     * Static type of this element
     *
     * @var string
     */
    public $fieldtype = "permissionResource";


    public function configureOptions () {
        $options = [
            ["key" => FrontendPermissionToolkit_Service::INHERIT, "value" => FrontendPermissionToolkit_Service::INHERIT],
            ["key" => FrontendPermissionToolkit_Service::ALLOW, "value" => FrontendPermissionToolkit_Service::ALLOW],
            ["key" => FrontendPermissionToolkit_Service::DENY, "value" => FrontendPermissionToolkit_Service::DENY]
        ];

        $this->setOptions($options);
    }


    protected function checkForEmpty($data) {
        if(empty($data)) {
            return FrontendPermissionToolkit_Service::INHERIT;
        }
        return $data;
    }

    /**
     * @see Object_Class_Data::getDataForResource
     * @param string $data
     * @param null|Object_Abstract $object
     * @return string
     */
    public function getDataForResource($data, $object = null, $params = []) {
        return $this->checkForEmpty($data);
    }

    /**
     * @see Object_Class_Data::getDataFromResource
     * @param string $data
     * @return string
     */
    public function getDataFromResource($data, $object = null, $params = []) {
        return $this->checkForEmpty($data);
    }

    /**
     * @see Object_Class_Data::getDataForQueryResource
     * @param string $data
     * @param null|Object_Abstract $object
     * @return string
     */
    public function getDataForQueryResource($data, $object = null, $params = []) {
        return $this->checkForEmpty($data);
    }


    public function __wakeup () {
        $this->configureOptions();
    }


    /**
     * @param $data
     * @return static
     */
    public static function __set_state($data)
    {
        $obj = parent::__set_state($data);
        $obj->configureOptions();
        return $obj;
    }
}
