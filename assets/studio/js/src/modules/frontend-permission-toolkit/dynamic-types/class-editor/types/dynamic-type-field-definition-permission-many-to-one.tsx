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
import { DynamicTypeFieldDefinitionManyToOne } from '@pimcore/studio-ui-bundle/modules/field-definitions'
import { type ElementIcon } from '@pimcore/studio-ui-bundle/components'

export class DynamicTypeFieldDefinitionPermissionManyToOne extends DynamicTypeFieldDefinitionManyToOne {
  id: string = 'permissionManyToOneRelation'

  getIcon (): ElementIcon {
    return { type: 'name', value: 'permission-many-to-one' }
  }

  getGroup (): string[] {
    return ['data', 'frontend-permission-toolkit']
  }
}
