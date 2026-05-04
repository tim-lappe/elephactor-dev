#!/usr/bin/env bash
set -euo pipefail
if [[ -z "${ELEPHACTOR_DIST_TOKEN:-}" ]]; then
  echo "ELEPHACTOR_DIST_TOKEN is required." >&2
  exit 1
fi
E_REMOTE="https://x-access-token:${ELEPHACTOR_DIST_TOKEN}@github.com/tim-lappe/elephactor.git"
V_REMOTE="https://x-access-token:${ELEPHACTOR_DIST_TOKEN}@github.com/tim-lappe/elephactor-vscode.git"

collect_versions() {
  local url
  for url in "${E_REMOTE}" "${V_REMOTE}"; do
    git ls-remote --tags "${url}" | awk '$2 ~ /^refs\/tags\/v[0-9]+\.[0-9]+\.[0-9]+$/ { sub(/^refs\/tags\/v/, "", $2); print $2 }' || true
  done
}

max=""
while read -r ver; do
  [[ -z "${ver}" ]] && continue
  if [[ -z "${max}" ]]; then
    max="${ver}"
  else
    max="$(printf '%s\n' "${max}" "${ver}" | sort -V | tail -1)"
  fi
done < <(collect_versions | sort -u)

if [[ -z "${max}" ]]; then
  next="0.1.0"
else
  IFS=. read -r maj min _pat <<< "${max}"
  min=$((min + 1))
  next="${maj}.${min}.0"
fi

echo "Latest semver tag across dist repos: ${max:-<none>}"
echo "Next release version: ${next}"

if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
  {
    echo "version=${next}"
  } >>"${GITHUB_OUTPUT}"
fi
