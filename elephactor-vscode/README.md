# Elephactor VS Code Extension

Elephactor keeps PHP class moves and renames in sync with the Elephactor CLI. When a PHP file is renamed or moved in VS Code, the extension asks the CLI to update class declarations and references while skipping the physical file operation that VS Code already performed.

The extension also contributes the `Elephactor: Install Elephactor` command, which installs `tim-lappe/elephactor` into the nearest Composer project.

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

## Available scripts

- `npm run compile` compiles the TypeScript sources into `dist`.
- `npm run watch` keeps the compiler running for iterative development.
- `npm run check` performs a no-emit type check.
- `npm run test` executes the extension tests via `@vscode/test-cli`.
- `npm run package` creates a `.vsix` artifact using `@vscode/vsce`.

## Extension structure

- `src/extension.ts`: activation entry point and disposable registration.
- `src/installCommand.ts`: `Elephactor: Install Elephactor` command.
- `src/phpFileRename.ts`: PHP file move and rename orchestration.
- `src/composerProject.ts`: Composer project root discovery.
- `src/elephactorCli.ts`: Elephactor binary resolution and CLI execution.
- `.vscode/`: launch and task configs for debugging and building.
- `.vscodeignore`: excludes dev-only files from packaged artifacts.
- `vsc-extension-quickstart.md`: handy reminders for extension development.

## Next steps

- Expand the test suite by adding files in `src/test` and running `npm run test`.

