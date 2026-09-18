#### v2026.2.1
- The compiled Studio frontend is no longer committed as an expanded `src/Resources/public/studio/build/` directory.
  It now ships as a single archive (`build-dist/build-<id>.zip`) that is extracted into
  `src/Resources/public/studio/build/` automatically during cache warmup.
- `pimcore/studio-ui-bundle` `^2026.2.1` is now required, as it provides the archive extraction.
- Read-only filesystem deployments must run `bin/console cache:warmup` (or `cache:clear`) during the
  build/deploy phase while the bundle directory (usually under `vendor/`) is still writable.

# Upgrade Information

Following steps are necessary during updating to newer versions.

## Upgrade to 2026.1.0
- Added support to `PHP` `8.5`.
- Removed support to `PHP` `8.3` and Symfony `v6`.
