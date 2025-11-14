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
import { List } from 'antd'
import { useTranslation } from '@pimcore/studio-ui-bundle/app'
import { SanitizeHtml, Flex, Tag } from '@pimcore/studio-ui-bundle/components'
import { GridCellPreviewWrapper } from '@pimcore/studio-ui-bundle/modules/element'
import { type PermissionResource, type PermissionOption } from '../../types/dynamic-type-object-data-dynamic-permission-resource'

export interface PermissionValue {
  label: string
  option: string
  permission: string
}

export interface DynamicPermissionResourcePreviewProps {
  value?: PermissionValue[]
  permissionResources: PermissionResource[]
  permissionOptions: PermissionOption[]
}

interface TableDataType {
  key: string
  label: string
  option: string
  permission: string
}

export const DynamicPermissionResourcePreview = (props: DynamicPermissionResourcePreviewProps): React.JSX.Element => {
  const { permissionResources, value = [] } = props
  const { t } = useTranslation()

  const currentValues = new Map<string, string>()
  value.forEach(item => {
    currentValues.set(item.option, item.permission)
  })

  const tableData: TableDataType[] = permissionResources.map(resource => ({
    key: resource.value,
    label: resource.label,
    option: resource.value,
    permission: currentValues.get(resource.value) ?? 'inherit'
  }))

  return (
    <GridCellPreviewWrapper>
      <List
        className="w-full"
        dataSource={ tableData }
        renderItem={ (item) => (
          <List.Item>
            <Flex
              align="center"
              className="w-full"
              gap="normal"
              justify="space-between"
            >
              <SanitizeHtml html={ item.label } />
              <Tag>{t(item.permission)}</Tag>
            </Flex>
          </List.Item>
        ) }
        size="small"
      />
    </GridCellPreviewWrapper>
  )
}
