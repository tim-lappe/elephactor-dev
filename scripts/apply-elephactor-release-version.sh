#!/usr/bin/env bash
set -euo pipefail
v="${1:?version argument required}"
root="$(cd "$(dirname "$0")/.." && pwd)"
jq --arg ver "$v" '.version = $ver' "${root}/elephactor/composer.json" >"${root}/elephactor/composer.json.tmp"
mv "${root}/elephactor/composer.json.tmp" "${root}/elephactor/composer.json"
jq --arg ver "$v" '.version = $ver' "${root}/elephactor/composer.dist.json" >"${root}/elephactor/composer.dist.json.tmp"
mv "${root}/elephactor/composer.dist.json.tmp" "${root}/elephactor/composer.dist.json"
