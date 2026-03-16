/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

/**
 * Service IDs for the Frontend Permission Toolkit Bundle
 * Centralized location for all dependency injection service identifiers
 */
export const bundleServiceIds = {
  'FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionResource': 'FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionResource',
  'FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionManyToOneRelation': 'FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionManyToOneRelation',
  'FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionManyToManyRelation': 'FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionManyToManyRelation',
  'FrontendPermissionToolkit/DynamicTypes/ObjectData/DynamicPermissionResource': 'FrontendPermissionToolkit/DynamicTypes/ObjectData/DynamicPermissionResource',
  'FrontendPermissionToolkit/ClassEditor/PermissionResource': 'FrontendPermissionToolkit/ClassEditor/PermissionResource',
  'FrontendPermissionToolkit/ClassEditor/PermissionManyToManyRelation': 'FrontendPermissionToolkit/ClassEditor/PermissionManyToManyRelation',
  'FrontendPermissionToolkit/ClassEditor/PermissionManyToOneRelation': 'FrontendPermissionToolkit/ClassEditor/PermissionManyToOneRelation',
  'FrontendPermissionToolkit/ClassEditor/DynamicPermissionResource': 'FrontendPermissionToolkit/ClassEditor/DynamicPermissionResource'
} as const
