import * as vscode from "vscode";
import { commandIds, elephactorComposerPackage, installAction } from "./constants";
import { resolveComposerRootForInstall } from "./composerProject";

export type ElephactorInstallTarget = "global" | "project";

type ElephactorInstallPick = vscode.QuickPickItem & {
  target: ElephactorInstallTarget;
};

const installPicks: ElephactorInstallPick[] = [
  {
    label: "Install globally",
    description: composerInstallCommand("global"),
    detail: "Makes the elephactor command available without adding it to each project.",
    target: "global",
  },
  {
    label: "Install as project dev dependency",
    description: composerInstallCommand("project"),
    detail: "Adds Elephactor to require-dev in the nearest Composer project.",
    target: "project",
  },
];

export function registerInstallCommand(): vscode.Disposable {
  return vscode.commands.registerCommand(
    commandIds.installElephactor,
    async (uri?: vscode.Uri) => {
      await installElephactor(uri);
    },
  );
}

export async function installElephactor(uri?: vscode.Uri): Promise<void> {
  const installTarget = await selectInstallTarget();
  if (installTarget === undefined) {
    return;
  }

  const terminalOptions: vscode.TerminalOptions = {
    name: installTarget === "global" ? "Elephactor Global Install" : "Elephactor Project Install",
  };

  if (installTarget === "project") {
    const composerRoot = await resolveComposerRootForInstall(uri);
    if (composerRoot === undefined) {
      vscode.window.showErrorMessage("Could not find composer.json for project Elephactor installation.");
      return;
    }

    terminalOptions.cwd = composerRoot;
  }

  const terminal = vscode.window.createTerminal({
    ...terminalOptions,
  });
  terminal.show();
  terminal.sendText(composerInstallCommand(installTarget));
}

export async function promptToInstallElephactor(uri: vscode.Uri): Promise<void> {
  const selectedAction = await vscode.window.showWarningMessage(
    "Elephactor is not installed. Install it globally or as a project dev dependency to enable PHP refactorings.",
    installAction,
  );

  if (selectedAction === installAction) {
    await vscode.commands.executeCommand(commandIds.installElephactor, uri);
  }
}

export function composerInstallCommand(target: ElephactorInstallTarget): string {
  if (target === "global") {
    return `composer global require ${elephactorComposerPackage}:@dev`;
  }

  return `composer require --dev ${elephactorComposerPackage}`;
}

async function selectInstallTarget(): Promise<ElephactorInstallTarget | undefined> {
  const selectedPick = await vscode.window.showQuickPick(installPicks, {
    placeHolder: "How do you want to install Elephactor?",
  });

  return selectedPick?.target;
}
