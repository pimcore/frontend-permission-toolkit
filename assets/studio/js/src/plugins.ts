/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

import { type IAbstractPlugin } from '@pimcore/studio-ui-bundle'
import { type DynamicTypeFieldDefinitionRegistry } from '@pimcore/studio-ui-bundle/modules/field-definitions'
import { FrontEndPermissionToolkitModule } from './modules/frontend-permission-toolkit'
import { bundleServiceIds } from './config/service-ids'
import { DynamicTypeObjectDataPermissionResource } from './modules/frontend-permission-toolkit/dynamic-types/object-data/types/dynamic-type-object-data-permission-resource'
import { DynamicTypeObjectDataPermissionManyToOneRelation } from './modules/frontend-permission-toolkit/dynamic-types/object-data/types/dynamic-type-object-data-permission-many-to-one-relation'
import { DynamicTypeObjectDataPermissionManyToManyRelation } from './modules/frontend-permission-toolkit/dynamic-types/object-data/types/dynamic-type-object-data-permission-many-to-many-relation'
import { DynamicTypeObjectDataDynamicPermissionResource } from './modules/frontend-permission-toolkit/dynamic-types/object-data/types/dynamic-type-object-data-dynamic-permission-resource'
import { DynamicTypeFieldDefinitionDynamicPermissionResource } from './modules/frontend-permission-toolkit/dynamic-types/class-editor/types/dynamic-type-field-definition-dynamic-permission-resource'
import { DynamicTypeFieldDefinitionPermissionManyToMany } from './modules/frontend-permission-toolkit/dynamic-types/class-editor/types/dynamic-type-field-definition-permission-many-to-many'
import { DynamicTypeFieldDefinitionPermissionManyToOne } from './modules/frontend-permission-toolkit/dynamic-types/class-editor/types/dynamic-type-field-definition-permission-many-to-one'
import { DynamicTypeFieldDefinitionPermissionResource } from './modules/frontend-permission-toolkit/dynamic-types/class-editor/types/dynamic-type-field-definition-permission-resource'

if (module.hot !== undefined) {
  module.hot.accept()
}

export const FrontEndPermissionToolkitPlugin: IAbstractPlugin = {
  name: 'pimcore-frontendpermissiontoolkit-plugin',

  // Register and overwrite services here
  onInit: ({ container }): void => {
    container.bind(bundleServiceIds['FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionResource']).to(DynamicTypeObjectDataPermissionResource).inSingletonScope()
    container.bind(bundleServiceIds['FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionManyToOneRelation']).to(DynamicTypeObjectDataPermissionManyToOneRelation).inSingletonScope()
    container.bind(bundleServiceIds['FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionManyToManyRelation']).to(DynamicTypeObjectDataPermissionManyToManyRelation).inSingletonScope()
    container.bind(bundleServiceIds['FrontendPermissionToolkit/DynamicTypes/ObjectData/DynamicPermissionResource']).to(DynamicTypeObjectDataDynamicPermissionResource).inSingletonScope()

    container.bind(bundleServiceIds['FrontendPermissionToolkit/ClassEditor/PermissionResource']).to(DynamicTypeFieldDefinitionDynamicPermissionResource).inSingletonScope()
    container.bind(bundleServiceIds['FrontendPermissionToolkit/ClassEditor/PermissionManyToManyRelation']).to(DynamicTypeFieldDefinitionPermissionManyToMany).inSingletonScope()
    container.bind(bundleServiceIds['FrontendPermissionToolkit/ClassEditor/PermissionManyToOneRelation']).to(DynamicTypeFieldDefinitionPermissionManyToOne).inSingletonScope()
    container.bind(bundleServiceIds['FrontendPermissionToolkit/ClassEditor/DynamicPermissionResource']).to(DynamicTypeFieldDefinitionPermissionResource).inSingletonScope()

    const fieldDefinitionRegistry = container.get<DynamicTypeFieldDefinitionRegistry>('DynamicTypes/FieldDefinitionRegistry')
    fieldDefinitionRegistry.registerDropdownGroupInfo('data/frontend-permission-toolkit', {
      icon: { type: 'name', value: 'shield' },
      translationKey: 'field-definition.groups.data.frontend-permission-toolkit'
    })

    fieldDefinitionRegistry.registerDynamicType(container.get(bundleServiceIds['FrontendPermissionToolkit/ClassEditor/PermissionResource']))
    fieldDefinitionRegistry.registerDynamicType(container.get(bundleServiceIds['FrontendPermissionToolkit/ClassEditor/DynamicPermissionResource']))
    fieldDefinitionRegistry.registerDynamicType(container.get(bundleServiceIds['FrontendPermissionToolkit/ClassEditor/PermissionManyToOneRelation']))
    fieldDefinitionRegistry.registerDynamicType(container.get(bundleServiceIds['FrontendPermissionToolkit/ClassEditor/PermissionManyToManyRelation']))
  },

  // register modules here
  onStartup: ({ moduleSystem }): void => {
    moduleSystem.registerModule(FrontEndPermissionToolkitModule)
    console.log('Hello from front end permission toolkit.')
  }
}
