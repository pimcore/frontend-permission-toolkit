/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

import React from 'react'
import {
  DynamicTypeObjectDataAbstract,
  type EditModalSettings,
  type AbstractObjectDataDefinition,
  type EditMode,
  type GetGridCellDefinitionProps
} from '@pimcore/studio-ui-bundle/modules/element'
import { DynamicPermissionResource } from '../components/dynamic-permission-resource/dynamic-permission-resource'
import { DynamicPermissionResourcePreview } from '../components/dynamic-permission-resource/dynamic-permission-resource-preview'

export interface PermissionResource {
  value: string
  label: string
}

export interface PermissionOption {
  key: string
  value: string
}

export interface DynamicPermissionResourceDefinition extends AbstractObjectDataDefinition {
  permissionResources: PermissionResource[]
  permissionOptions: PermissionOption[]
}

export class DynamicTypeObjectDataDynamicPermissionResource extends DynamicTypeObjectDataAbstract {
  id: string = 'dynamicPermissionResource'
  gridCellEditMode: EditMode = 'edit-modal'

  gridCellEditModalSettings: EditModalSettings = {
    modalSize: 'XL',
    formLayout: 'vertical'
  }

  getObjectDataComponent (props: DynamicPermissionResourceDefinition): React.ReactElement<AbstractObjectDataDefinition> {
    return (
      <DynamicPermissionResource
        disabled={ props.noteditable === true }
        permissionOptions={ props.permissionOptions ?? [] }
        permissionResources={ props.permissionResources ?? [] }
      />
    )
  }

  getGridCellPreviewComponent (props: GetGridCellDefinitionProps): React.ReactElement {
    const value = props.cellProps.getValue()
    const fieldDefinition: DynamicPermissionResourceDefinition = props.objectProps as DynamicPermissionResourceDefinition

    return (
      <DynamicPermissionResourcePreview
        permissionOptions={ fieldDefinition.permissionOptions ?? [] }
        permissionResources={ fieldDefinition.permissionResources ?? [] }
        value={ value }
      />
    )
  }

  getDefaultGridColumnWidth (): number | undefined {
    return 800
  }
}
