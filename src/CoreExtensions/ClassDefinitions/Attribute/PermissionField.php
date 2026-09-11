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

namespace FrontendPermissionToolkitBundle\CoreExtensions\ClassDefinitions\Attribute;

use Attribute;

/**
 * Marks a data type as one of the toolkit's permission field types - the fields
 * {@see \FrontendPermissionToolkitBundle\Service} resolves permissions from.
 *
 * Every permission data type this bundle registers carries it, and the `usage.*` telemetry walk recognises
 * permission fields by it rather than by a list of type names, so a new permission type is picked up the
 * moment it carries this attribute. Subclasses of a marked type count as well.
 *
 * @internal
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class PermissionField
{
}
