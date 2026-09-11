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

namespace FrontendPermissionToolkitBundle\Telemetry;

/**
 * Whether the customer's data model carries one of the toolkit's permission field types.
 *
 * @internal
 */
interface PermissionFieldsInterface
{
    /**
     * @return bool|null null when part of the data model could not be read and no field was found elsewhere
     */
    public function exist(): ?bool;
}
