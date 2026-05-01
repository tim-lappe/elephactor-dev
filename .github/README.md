# GitHub Actions for this repository

## Dist mirror secret (`ELEPHACTOR_DIST_TOKEN`)

The workflow [`.github/workflows/elephactor.yml`](workflows/elephactor.yml) pushes the Composer package subtree to [tim-lappe/elephactor](https://github.com/tim-lappe/elephactor) on every successful push to `main` on `tim-lappe/elephactor-dev`.

Configure a **repository secret** named `ELEPHACTOR_DIST_TOKEN` on **elephactor-dev** with a token that can push to the target repo.

**Fine-grained personal access token (recommended)**

- Resource owner: your user (or the org that owns the repo)
- Repository access: only `tim-lappe/elephactor`
- Permissions: **Contents** — Read and write
- For a user-owned repo, that is enough to `git push` over HTTPS.

**Classic PAT alternative**

- Scope: `repo` (full control of private repositories), or at minimum access that allows pushing to `tim-lappe/elephactor`.

Without this secret, the `sync` job fails at clone with a clear error so you notice misconfiguration before merging.
