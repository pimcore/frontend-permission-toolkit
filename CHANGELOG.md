# Upgrade Information

Following steps are necessary during updating to newer versions.

## Upgrade to 2026.1.0

### PHP and Dependency Requirements

- Added support for `PHP` `8.5`.
- Removed support for `PHP` `8.3`.

### Removed ExtJS / Admin Classic

- `FrontendPermissionToolkitBundle` no longer implements `PimcoreBundleAdminClassicInterface`.
- Removed `BundleAdminClassicTrait` and the associated `getCssPaths()` / `getJsPaths()` methods.
- All legacy JS files (`startup.js`, all `datatypes/` JS) and `backend.css` have been deleted.
- The bundle now unconditionally loads `studio_backend.yaml` and `studio_ui.yaml` (conditional loading removed).

### Studio UI Field Definitions

- New Studio UI dynamic type components for the class editor have been introduced:
  - `DynamicTypeFieldDefinitionPermissionResource`
  - `DynamicTypeFieldDefinitionPermissionManyToMany`
  - `DynamicTypeFieldDefinitionPermissionManyToOne`
  - `DynamicTypeFieldDefinitionDynamicPermissionResource`
