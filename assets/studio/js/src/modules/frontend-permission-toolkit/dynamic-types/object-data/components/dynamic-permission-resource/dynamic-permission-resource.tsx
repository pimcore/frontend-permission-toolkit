/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

import React, { useMemo } from 'react'
import { Table } from 'antd'
import { useTranslation } from '@pimcore/studio-ui-bundle/app'
import { SanitizeHtml, Select } from '@pimcore/studio-ui-bundle/components'
import { type PermissionResource, type PermissionOption } from '../../types/dynamic-type-object-data-dynamic-permission-resource'
import { useFieldWidth } from '@pimcore/studio-ui-bundle/modules/element'

export interface PermissionValue {
  label: string
  option: string
  permission: string
}

export interface DynamicPermissionResourceProps {
  permissionResources: PermissionResource[]
  permissionOptions: PermissionOption[]
  value?: PermissionValue[]
  disabled?: boolean
  onChange?: (value: PermissionValue[]) => void
}

interface TableDataType {
  key: string
  label: string
  option: string
  permission: string
}

export const DynamicPermissionResource = (props: DynamicPermissionResourceProps): React.JSX.Element => {
  const { permissionResources, permissionOptions, value = [], disabled = false, onChange } = props
  const fieldWidth = useFieldWidth()
  const { t } = useTranslation()

  const currentValues = useMemo(() => {
    const map = new Map<string, string>()
    value.forEach(item => {
      map.set(item.option, item.permission)
    })
    return map
  }, [value])

  const tableData: TableDataType[] = useMemo(() => {
    return permissionResources.map(resource => ({
      key: resource.value,
      label: resource.label,
      option: resource.value,
      permission: currentValues.get(resource.value) ?? 'inherit'
    }))
  }, [permissionResources, currentValues])

  const handlePermissionChange = (option: string, newPermission: string): void => {
    if (onChange === null || onChange === undefined) return

    const updatedValues = value.filter(item => item.option !== option)

    const resource = permissionResources.find(r => r.value === option)
    if (resource !== null && resource !== undefined) {
      updatedValues.push({
        label: resource.label,
        option,
        permission: newPermission
      })
    }

    onChange(updatedValues)
  }

  const columns = [
    {
      dataIndex: 'label',
      key: 'label',
      render: (label: string) => (
        <SanitizeHtml html={ label } />
      )
    },
    {
      dataIndex: 'permission',
      key: 'permission',
      width: 150,
      render: (permission: string, record: TableDataType) => (
        <Select
          disabled={ disabled }
          onChange={ (newPermission: string) => { handlePermissionChange(record.option, newPermission) } }
          options={ permissionOptions.map(option => ({
            label: t(option.key),
            value: option.value
          })) }
          style={ { width: '100%' } }
          value={ permission }
        />
      )
    }
  ]

  return (
    <div style={ { width: '100%', maxWidth: fieldWidth.large + 'px' } }>
      <Table
        bordered
        columns={ columns }
        dataSource={ tableData }
        pagination={ false }
        showHeader={ false }
        size="small"
        tableLayout="auto"
      />
    </div>
  )
}
