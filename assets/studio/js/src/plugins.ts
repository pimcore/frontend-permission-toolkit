import { type IAbstractPlugin } from '@pimcore/studio-ui-bundle'
import { FrontEndPermissionToolkitModule } from './modules/frontend-permission-toolkit'

if (module.hot !== undefined) {
  module.hot.accept()
}

export const FrontEndPermissionToolkitPlugin: IAbstractPlugin = {
  name: 'pimcore-frontendpermissiontoolkit-plugin',

  // Register and overwrite services here
  onInit: ({ container }): void => {

  },

  // register modules here
  onStartup: ({ moduleSystem }): void => {
    moduleSystem.registerModule(FrontEndPermissionToolkitModule)
    console.log('Hello from front end permission toolkit.')
  }
}
