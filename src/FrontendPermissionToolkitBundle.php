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

namespace FrontendPermissionToolkitBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class FrontendPermissionToolkitBundle extends AbstractPimcoreBundle {

    public function getCssPaths() {
        return [
            '/bundles/frontendpermissiontoolkit/css/backend.css'
        ];
    }

    public function getJsPaths() {
        return [
            '/bundles/frontendpermissiontoolkit/js/startup.js',
            '/bundles/frontendpermissiontoolkit/js/datatypes/classes/data/permissionResource.js',
            '/bundles/frontendpermissiontoolkit/js/datatypes/tags/permissionResource.js',
            '/bundles/frontendpermissiontoolkit/js/datatypes/classes/data/permissionObjects.js',
            '/bundles/frontendpermissiontoolkit/js/datatypes/tags/permissionObjects.js',
            '/bundles/frontendpermissiontoolkit/js/datatypes/classes/data/permissionHref.js',
            '/bundles/frontendpermissiontoolkit/js/datatypes/tags/permissionHref.js',
        ];
    }


//
//	public static function install (){
//        // implement your own logic here
//        return true;
//	}
//
//	public static function uninstall (){
//        // implement your own logic here
//        return true;
//	}
//
//	public static function isInstalled () {
//        // implement your own logic here
//        return true;
//	}

}
