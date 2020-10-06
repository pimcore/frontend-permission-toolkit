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

namespace FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Interfaces;



use Pimcore\Model\DataObject\ClassDefinition\Data;

interface DataProviderInterface
{

    /**
     * @param array $context
     * @param Data $fieldDefinition
     * @return array - array of array like ['value' => 'permission1', 'label' => 'some nice label' ]
     */
    public function getPermissionResources(array $context, Data $fieldDefinition): array;

}