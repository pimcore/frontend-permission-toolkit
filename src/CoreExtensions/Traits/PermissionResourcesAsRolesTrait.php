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

namespace FrontendPermissionToolkitBundle\CoreExtensions\Traits;

use FrontendPermissionToolkitBundle\Service;

trait PermissionResourcesAsRolesTrait
{

    /**
     * Returns all allowed permission resources of current object prefixed with ROLE_
     *
     * @return string[]
     */
    public function getRoles(): array {
        /**
         * @var $service Service
         */
        $service = \Pimcore::getContainer()->get(Service::class);
        $permissions = $service->getPermissions($this);

        $roles = [];
        foreach($permissions as $permission => $allowed) {
            if($allowed === Service::ALLOW) {
                $roles[] = "ROLE_" . $permission;
            }
        }

        return $roles;
    }

}
