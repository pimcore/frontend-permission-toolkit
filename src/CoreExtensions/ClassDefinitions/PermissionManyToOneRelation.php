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

namespace FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions;

use Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation;

class PermissionManyToOneRelation extends ManyToOneRelation
{
    /**
     * @deprecated Will be removed in frontend-permission-toolkit 4, use getFieldType() instead.
     */
    public string $fieldtype = 'permissionManyToOneRelation';

    public function getFieldType(): string
    {
        return 'permissionManyToOneRelation';
    }
}
