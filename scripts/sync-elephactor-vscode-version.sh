#!/usr/bin/env bash
set -euo pipefail
root="$(cd "$(dirname "$0")/.." && pwd)"
v="$(jq -r .version "$root/elephactor/composer.json")"
if [[ -z "$v" || "$v" == "null" ]]; then
  echo "elephactor/composer.json must contain a non-empty version string." >&2
  exit 1
fi
jq --arg ver "$v" '.version = $ver' \
  "$root/elephactor-vscode/package.json" >"$root/elephactor-vscode/package.json.tmp"
mv "$root/elephactor-vscode/package.json.tmp" "$root/elephactor-vscode/package.json"
jq --arg ver "$v" '.version = $ver | .packages[""].version = $ver' \
  "$root/elephactor-vscode/package-lock.json" >"$root/elephactor-vscode/package-lock.json.tmp"
mv "$root/elephactor-vscode/package-lock.json.tmp" "$root/elephactor-vscode/package-lock.json"
