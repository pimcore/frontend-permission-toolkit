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

namespace FrontendPermissionToolkitBundle\Telemetry;

use Pimcore\Telemetry\Usage\BundleUsageProviderInterface;

/**
 * Is the Frontend Permission Toolkit set up, or merely installed?
 *
 * "Used" means at least one data-object class **of the customer's own**, or an object brick, carries one of
 * the toolkit's permission field types. The bundle has no storage and no settings; adding one of its data
 * types to a class definition *is* the way it gets set up. This is the L3 question every `usage.*` key
 * answers; whether any user object actually holds permissions is the exercised fact and deliberately not
 * what this key reports.
 *
 * Why "of the customer's own": Portal Engine requires this bundle and its installer creates `PortalUser`
 * and `PortalUserGroup` with permission fields already on them. Without the exclusion, every Portal Engine
 * install would read as having set the toolkit up - the always-true trap this namespace exists to avoid.
 * Those two classes are the only ones any in-tree bundle ships with these field types; if another bundle
 * starts to, it belongs on the list in {@see ClassDefinitionPermissionFields}.
 *
 * The data model is read through {@see PermissionFieldsInterface}, one walk over raw class and brick
 * definitions per snapshot, no hydration and no user.
 *
 * Content-never: a boolean. Class and field names are never emitted.
 *
 * @internal
 */
final readonly class FrontendPermissionToolkitUsageProvider implements BundleUsageProviderInterface
{
    public function __construct(
        private PermissionFieldsInterface $fields,
    ) {
    }

    public function getBundleKey(): string
    {
        return 'frontend_permission_toolkit';
    }

    public function isUsed(): ?bool
    {
        return $this->fields->exist();
    }
}
