# Elephactor VS Code Extension

Elephactor keeps PHP class moves and renames in sync with the Elephactor CLI. When a PHP file is renamed or moved in VS Code, the extension asks the CLI to update class declarations and references while skipping the physical file operation that VS Code already performed.

The extension also contributes the `Elephactor: Install Elephactor` command, which can install `tim-lappe/elephactor` globally or into the nearest Composer project.

## Getting started

1. Install dependencies:

   ```bash
   npm install
   ```

2. Build once or start watch mode:

   ```bash
   npm run compile
   # or
   npm run watch
   ```

3. Press `F5` in VS Code to launch an Extension Development Host, then move or rename a PHP class file in a Composer project.

## Installing Elephactor

Run `Elephactor: Install Elephactor` from the command palette and choose one of the install modes:

- `Install globally` runs `composer global require tim-lappe/elephactor:@dev`. This makes the `elephactor` command available without adding a dependency to each project.
- `Install as project dev dependency` runs `composer require --dev tim-lappe/elephactor` in the nearest Composer project.

For global installs, the extension first tries `elephactor` from VS Code's `PATH`. If that is not available, it asks Composer for the global bin directory and runs Elephactor from there.

## Available scripts

- `npm run compile` compiles the TypeScript sources into `dist`.
- `npm run watch` keeps the compiler running for iterative development.
- `npm run check` performs a no-emit type check.
- `npm run test` executes the extension tests via `@vscode/test-cli`.
- `npm run package` creates a `.vsix` artifact using `@vscode/vsce`.

## Extension structure

- `src/extension.ts`: activation entry point and disposable registration.
- `src/installCommand.ts`: `Elephactor: Install Elephactor` command and install-mode selection.
- `src/phpFileRename.ts`: PHP file move and rename orchestration.
- `src/composerProject.ts`: Composer project root discovery.
- `src/elephactorCli.ts`: Elephactor binary resolution and CLI execution.
- `.vscode/`: launch and task configs for debugging and building.
- `.vscodeignore`: excludes dev-only files from packaged artifacts.
- `vsc-extension-quickstart.md`: handy reminders for extension development.

## Next steps

- Expand the test suite by adding files in `src/test` and running `npm run test`.

