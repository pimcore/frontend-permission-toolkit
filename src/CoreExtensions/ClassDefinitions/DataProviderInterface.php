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
    'Interface `FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\DataProviderInterface` is deprecated since version 1.3.0 and will be removed in 2.0.0. ' .
    'Use `' . \FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Interfaces\DataProviderInterface::class . '` instead.',
    E_USER_DEPRECATED
);

/**
 * @deprecated use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Interfaces\DataProviderInterface instead
 */
interface DataProviderInterface extends \FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Interfaces\DataProviderInterface
{

}
