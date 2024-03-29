<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions;

use Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation;

class PermissionManyToManyRelation extends ManyToManyObjectRelation
{
    /**
     * @deprecated Will be removed in frontend-permission-toolkit 3, use getFieldType() instead.
     */
    public string $fieldtype = 'permissionManyToManyRelation';

    public function getFieldType(): string
    {
        return 'permissionManyToManyRelation';
    }
}
