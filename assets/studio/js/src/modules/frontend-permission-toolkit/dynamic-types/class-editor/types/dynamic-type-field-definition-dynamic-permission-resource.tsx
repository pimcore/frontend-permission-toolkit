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
import { DynamicTypeFieldDefinitionDataAbstract, type FieldDefinitionContext } from '@pimcore/studio-ui-bundle/modules/field-definitions'
import { type ElementIcon } from '@pimcore/studio-ui-bundle/components'
import { DynamicPermissionResourceSettings } from '../settings/dynamic-permission-resource-settings'

export class DynamicTypeFieldDefinitionDynamicPermissionResource extends DynamicTypeFieldDefinitionDataAbstract {
  id: string = 'dynamicPermissionResource'

  getIcon (): ElementIcon {
    return { type: 'name', value: 'permission-resource' }
  }

  getGroup (): string[] {
    return ['data', 'frontend-permission-toolkit']
  }

  getFormFields (context: FieldDefinitionContext): React.JSX.Element {
    return super.getFormFields({ ...context, hideUnique: true })
  }

  getSpecificFormFields (context: FieldDefinitionContext): React.JSX.Element {
    const id = this.getId(context)
    const fieldDefinition = context.fieldDefinitions?.[id]

    return (
      <DynamicPermissionResourceSettings context={ context } id={ fieldDefinition?.name ?? id } type={ this.id } />
    )
  }
}
