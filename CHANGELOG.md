# Upgrade Information

Following steps are necessary during updating to newer versions.

## Upgrade to 2026.3.0
- Requires `pimcore/pimcore` `^2026.3`: the bundle registers a `usage.*` telemetry provider on the
  `Pimcore\Telemetry\Usage\BundleUsageProviderInterface` extension point that core introduced in that version.
  It reports whether one of the toolkit's permission field types is configured on a data-object class or object
  brick of your own (Portal Engine's shipped `PortalUser` and `PortalUserGroup` do not count); class and field
  names are never emitted.

## Upgrade to 2026.1.0
- Added support to `PHP` `8.5`.
- Removed support to `PHP` `8.3` and Symfony `v6`.
