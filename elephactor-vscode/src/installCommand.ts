import * as vscode from "vscode";
import { commandIds, installAction } from "./constants";
import { resolveComposerRootForInstall } from "./composerProject";

export function registerInstallCommand(): vscode.Disposable {
  return vscode.commands.registerCommand(
    commandIds.installElephactor,
    async (uri?: vscode.Uri) => {
      await installElephactor(uri);
    },
  );
}

export async function installElephactor(uri?: vscode.Uri): Promise<void> {
  const composerRoot = await resolveComposerRootForInstall(uri);
  if (composerRoot === undefined) {
    vscode.window.showErrorMessage("Could not find composer.json for Elephactor installation.");
    return;
  }

  const terminal = vscode.window.createTerminal({
    name: "Elephactor Install",
    cwd: composerRoot,
  });
  terminal.show();
  terminal.sendText("composer require --dev tim-lappe/elephactor");
}

export async function promptToInstallElephactor(uri: vscode.Uri): Promise<void> {
  const selectedAction = await vscode.window.showWarningMessage(
    "Elephactor is not installed in this Composer project.",
    installAction,
  );

  if (selectedAction === installAction) {
    await vscode.commands.executeCommand(commandIds.installElephactor, uri);
  }
}
