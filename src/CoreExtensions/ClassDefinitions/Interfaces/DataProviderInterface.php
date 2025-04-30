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

namespace FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Interfaces;

use Pimcore\Model\DataObject\ClassDefinition\Data;

interface DataProviderInterface
{
    /**
     * @param array $context
     * @param Data $fieldDefinition
     *
     * @return array - array of array like ['value' => 'permission1', 'label' => 'some nice label' ]
     */
    public function getPermissionResources(array $context, Data $fieldDefinition): array;
}
