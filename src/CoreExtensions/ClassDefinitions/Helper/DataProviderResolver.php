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

namespace FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Helper;

use FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Interfaces\DataProviderInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Data\Extension\ColumnType;
use Pimcore\Model\DataObject\ClassDefinition\Data\Extension\QueryColumnType;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ValidationException;
use Pimcore\Tool\Serialize;

class DataProviderResolver extends DataObject\ClassDefinition\Helper\ClassResolver
{
    /**
     * @param string $providerClass
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
