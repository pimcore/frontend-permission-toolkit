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

namespace FrontendPermissionToolkitBundle\CoreExtensions\Traits;

use FrontendPermissionToolkitBundle\Service;

trait PermissionResourcesAsRolesTrait
{
    /**
     * Returns all allowed permission resources of current object prefixed with ROLE_
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        /**
         * @var $service Service
         */
        $service = \Pimcore::getContainer()->get(Service::class);
        $permissions = $service->getPermissions($this);

        $roles = [];
        foreach ($permissions as $permission => $allowed) {
            if ($allowed === Service::ALLOW) {
                $roles[] = 'ROLE_' . $permission;
            }
        }

        return $roles;
    }
}
