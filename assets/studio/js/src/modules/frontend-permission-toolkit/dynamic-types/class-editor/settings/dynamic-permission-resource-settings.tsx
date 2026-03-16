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
import { useTranslation } from '@pimcore/studio-ui-bundle/app'
import { Form, Input } from '@pimcore/studio-ui-bundle/components'
import { type FieldDefinitionAbstractFormFieldsProps } from '@pimcore/studio-ui-bundle/modules/field-definitions'

export const DynamicPermissionResourceSettings = (props: FieldDefinitionAbstractFormFieldsProps): React.JSX.Element => {
  const { t } = useTranslation()

  return (
      <Form.Item
          label={ t('field-definition.dynamic-permission-resource.data-type') }
          name="dataProvider"
      >
          <Input />
      </Form.Item>
  )
}
