import { type IAbstractPlugin } from '@pimcore/studio-ui-bundle'
import { FrontEndPermissionToolkitModule } from './modules/frontend-permission-toolkit'
import { bundleServiceIds } from './config/service-ids'
import { DynamicTypeObjectDataPermissionResource } from './modules/frontend-permission-toolkit/dynamic-types/object-data/types/dynamic-type-object-data-permission-resource'
import { DynamicTypeObjectDataPermissionManyToOneRelation } from './modules/frontend-permission-toolkit/dynamic-types/object-data/types/dynamic-type-object-data-permission-many-to-one-relation'
import { DynamicTypeObjectDataPermissionManyToManyRelation } from './modules/frontend-permission-toolkit/dynamic-types/object-data/types/dynamic-type-object-data-permission-many-to-many-relation'
import { DynamicTypeObjectDataDynamicPermissionResource } from './modules/frontend-permission-toolkit/dynamic-types/object-data/types/dynamic-type-object-data-dynamic-permission-resource'

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
  },

  // register modules here
  onStartup: ({ moduleSystem }): void => {
    moduleSystem.registerModule(FrontEndPermissionToolkitModule)
    console.log('Hello from front end permission toolkit.')
  }
}
