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

@trigger_error(
    'Data-type `FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionObjects` is deprecated since version 1.1.0 and will be removed in 2.0.0. ' .
    'Use `' . PermissionManyToManyRelation::class . '` instead.',
    E_USER_DEPRECATED
);

class_exists(PermissionManyToManyRelation::class);

/**
 * @deprecated use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\PermissionManyToMany instead
 */
class PermissionObjects extends PermissionManyToManyRelation
{
}