#!/usr/bin/env bash
#
# Package the theme and plugin as installable .zip files.
#
# Output lands in dist/ and can be uploaded through
#   wp-admin -> Appearance -> Themes -> Add New -> Upload Theme
#   wp-admin -> Plugins -> Add New -> Upload Plugin
# on any WordPress host, with no SFTP or command line access needed.
#
# Usage:  ./bin/package.sh
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST="$ROOT/dist"

rm -rf "$DIST"
mkdir -p "$DIST"

# Exclusions: version control noise, macOS metadata, and editor droppings.
# The placeholder photos and brand assets ARE included — the seeder needs them.
EXCLUDES=(
  -x "*.DS_Store"
  -x "__MACOSX/*"
  -x "*/.git/*"
  -x "*.map"
)

echo "Packaging from $ROOT"
echo

cd "$ROOT/wp-content/themes"
zip -rq "$DIST/briteclean-theme.zip" briteclean "${EXCLUDES[@]}"
echo "  ✓ dist/briteclean-theme.zip        ($(du -h "$DIST/briteclean-theme.zip" | cut -f1))"

cd "$ROOT/wp-content/plugins"
zip -rq "$DIST/briteclean-bookings.zip" briteclean-bookings "${EXCLUDES[@]}"
echo "  ✓ dist/briteclean-bookings.zip     ($(du -h "$DIST/briteclean-bookings.zip" | cut -f1))"

echo
echo "Both zips are ready to upload through wp-admin."
echo "Install order: ACF and WooCommerce first, then the plugin, then the theme."
