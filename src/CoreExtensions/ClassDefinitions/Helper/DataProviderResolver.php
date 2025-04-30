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

namespace FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Helper;

use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Interfaces\DataProviderInterface;
use Pimcore\Model\DataObject;

class DataProviderResolver extends DataObject\ClassDefinition\Helper\ClassResolver
{
    /**
     * @param string $providerClass
     *
     * @return DataProviderInterface|null
     */
    public static function resolveDataProvider(string $providerClass): ?DataProviderInterface
    {
        $dataProvider = self::resolve($providerClass);

        if ($dataProvider instanceof DataProviderInterface) {
            return $dataProvider;
        }

        return null;
    }
}
