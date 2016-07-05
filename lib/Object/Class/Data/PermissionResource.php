<?php 
/**
 * Pimcore
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @category   Pimcore
 * @package    Object_Class
 * @copyright  Copyright (c) 2009-2013 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     New BSD License
 */

class Object_Class_Data_PermissionResource extends Object_Class_Data_Select {

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

   
}
