import * as path from "node:path";
import * as vscode from "vscode";
import { findComposerRoot } from "./composerProject";
import { BinaryResolution, resolveElephactorBinary, runElephactor } from "./elephactorCli";
import { promptToInstallElephactor } from "./installCommand";

export function registerPhpFileRenameHandler(outputChannel: vscode.OutputChannel): vscode.Disposable {
  return vscode.workspace.onDidRenameFiles(async (event) => {
    for (const file of event.files) {
      await handleRenamedFile(file, outputChannel);
    }
  });
}

export async function handleRenamedFile(
  file: vscode.FileRenameEvent["files"][number],
  outputChannel: vscode.OutputChannel,
): Promise<void> {
  if (!isPhpFile(file.oldUri) && !isPhpFile(file.newUri)) {
    return;
  }

  const composerRoot = await findComposerRoot(file.newUri);
  if (composerRoot === undefined) {
    vscode.window.showWarningMessage("Could not find composer.json for renamed PHP file.");
    return;
  }

  const binary = await resolveElephactorBinary(composerRoot);
  if (!binary.installed) {
    await handleMissingBinary(file.newUri, binary);
    return;
  }

  const commandSets = buildRenameCommandSets(file.oldUri.fsPath, file.newUri.fsPath);

  for (const args of commandSets) {
    const exitCode = await runElephactor(binary.binaryPath, composerRoot, args, outputChannel);
    if (exitCode !== 0) {
      vscode.window.showErrorMessage(`Elephactor failed while running ${args[0]}. See the Elephactor output for details.`);
      outputChannel.show(true);
      return;
    }
  }
}

export function buildRenameCommandSets(oldFilePath: string, newFilePath: string): string[][] {
  const oldDirectory = path.dirname(oldFilePath);
  const newDirectory = path.dirname(newFilePath);
  const oldClassName = path.basename(oldFilePath, path.extname(oldFilePath));
  const newClassName = path.basename(newFilePath, path.extname(newFilePath));
  const directoryChanged = oldDirectory !== newDirectory;
  const classNameChanged = oldClassName !== newClassName;
  const commandSets: string[][] = [];

  if (directoryChanged) {
    commandSets.push(["class:move", newFilePath, newDirectory, "--skip-file-move"]);
  }

  if (classNameChanged) {
    commandSets.push(["class:rename", oldClassName, newClassName, "--skip-file-rename"]);
  }

  return commandSets;
}

async function handleMissingBinary(uri: vscode.Uri, binary: BinaryResolution): Promise<void> {
  if (binary.configured) {
    vscode.window.showErrorMessage(`Configured Elephactor binary does not exist: ${binary.binaryPath}`);
    return;
  }

  await promptToInstallElephactor(uri);
}

function isPhpFile(uri: vscode.Uri): boolean {
  return uri.scheme === "file" && path.extname(uri.fsPath).toLowerCase() === ".php";
}
