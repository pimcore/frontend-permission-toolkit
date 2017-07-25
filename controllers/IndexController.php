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


class FrontendPermissionToolkit_IndexController extends Pimcore_Controller_Action_Admin {
    
    public function indexAction () {

        $object = Object_Concrete::getById(52);
        $permissions = FrontendPermissionToolkit_Service::getPermissions($object);
        p_r($permissions);


        p_r("is allowed res1: " . FrontendPermissionToolkit_Service::isAllowed($object, "res1"));
        p_r("is allowed res2: " . FrontendPermissionToolkit_Service::isAllowed($object, "res2"));
        p_r("is allowed resb1: " . FrontendPermissionToolkit_Service::isAllowed($object, "resb1"));
        p_r("is allowed resb3: " . FrontendPermissionToolkit_Service::isAllowed($object, "resb3"));
        p_r("is allowed res3: " . FrontendPermissionToolkit_Service::isAllowed($object, "res3"));
        p_r("is allowed res4: " . FrontendPermissionToolkit_Service::isAllowed($object, "res4"));

        // reachable via http://your.domain/plugin/FrontendPermissionToolkit/index/index

        die("sdf");
    }
}
