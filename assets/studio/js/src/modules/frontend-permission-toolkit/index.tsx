import { type AbstractModule, container } from '@pimcore/studio-ui-bundle'
import { serviceIds } from '@pimcore/studio-ui-bundle/app'
import { type DynamicTypeObjectDataRegistry } from '@pimcore/studio-ui-bundle/modules/element'
import { type DynamicTypeObjectDataPermissionResource } from './dynamic-types/object-data/types/dynamic-type-object-data-permission-resource'
import { type DynamicTypeObjectDataPermissionManyToOneRelation } from './dynamic-types/object-data/types/dynamic-type-object-data-permission-many-to-one-relation'
import { type DynamicTypeObjectDataPermissionManyToManyRelation } from './dynamic-types/object-data/types/dynamic-type-object-data-permission-many-to-many-relation'
import { type DynamicTypeObjectDataDynamicPermissionResource } from './dynamic-types/object-data/types/dynamic-type-object-data-dynamic-permission-resource'
import { bundleServiceIds } from '../../config/service-ids'

export const FrontEndPermissionToolkitModule: AbstractModule = {
  onInit: (): void => {
    const objectDataRegistry = container.get<DynamicTypeObjectDataRegistry>(serviceIds['DynamicTypes/ObjectDataRegistry'])
    const permissionResource = container.get<DynamicTypeObjectDataPermissionResource>(bundleServiceIds['FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionResource'])
    const permissionManyToOneRelation = container.get<DynamicTypeObjectDataPermissionManyToOneRelation>(bundleServiceIds['FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionManyToOneRelation'])
    const permissionManyToManyRelation = container.get<DynamicTypeObjectDataPermissionManyToManyRelation>(bundleServiceIds['FrontendPermissionToolkit/DynamicTypes/ObjectData/PermissionManyToManyRelation'])
    const dynamicPermissionResource = container.get<DynamicTypeObjectDataDynamicPermissionResource>(bundleServiceIds['FrontendPermissionToolkit/DynamicTypes/ObjectData/DynamicPermissionResource'])

    objectDataRegistry.registerDynamicType(permissionResource)
    objectDataRegistry.registerDynamicType(permissionManyToOneRelation)
    objectDataRegistry.registerDynamicType(permissionManyToManyRelation)
    objectDataRegistry.registerDynamicType(dynamicPermissionResource)
  }
}
