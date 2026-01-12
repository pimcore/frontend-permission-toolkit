<?php
declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace FrontendPermissionToolkitBundle\Grid\Column\Definition;

use Pimcore\Bundle\StudioBackendBundle\Grid\Column\Definition\DataObject\AbstractDefinition;

/**
 * @internal
 */
final readonly class PermissionManyToManyRelationDefinition extends AbstractDefinition
{
    public function getType(): string
    {
        return 'data-object.permissionManyToManyRelation';
    }

    public function getFrontendType(): string
    {
        return 'manyToManyRelation';
    }

    #[Override]
    public function isSortable(): bool
    {
        return false;
    }

    #[Override]
    public function isFilterable(): bool
    {
        return false;
    }
}
