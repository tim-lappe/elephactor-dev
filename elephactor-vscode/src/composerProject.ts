import * as fs from "node:fs/promises";
import * as path from "node:path";
import * as vscode from "vscode";

export async function findComposerRoot(uri: vscode.Uri): Promise<string | undefined> {
  const workspaceFolder = vscode.workspace.getWorkspaceFolder(uri);
  if (workspaceFolder === undefined) {
    return undefined;
  }

  return findComposerRootFromDirectory(path.dirname(uri.fsPath), workspaceFolder.uri.fsPath);
}

export async function resolveComposerRootForInstall(uri?: vscode.Uri): Promise<string | undefined> {
  if (uri !== undefined) {
    const composerRoot = await findComposerRoot(uri);
    if (composerRoot !== undefined) {
      return composerRoot;
    }
  }

  const activeEditorUri = vscode.window.activeTextEditor?.document.uri;
  if (activeEditorUri !== undefined) {
    const composerRoot = await findComposerRoot(activeEditorUri);
    if (composerRoot !== undefined) {
      return composerRoot;
    }
  }

  for (const workspaceFolder of vscode.workspace.workspaceFolders ?? []) {
    const composerRoot = await findComposerRootFromDirectory(workspaceFolder.uri.fsPath, workspaceFolder.uri.fsPath);
    if (composerRoot !== undefined) {
      return composerRoot;
    }
  }

  return undefined;
}

export async function findComposerRootFromDirectory(
  startDirectory: string,
  workspaceRoot: string,
): Promise<string | undefined> {
  let currentDirectory = path.resolve(startDirectory);
  const resolvedWorkspaceRoot = path.resolve(workspaceRoot);

  while (isInsideOrEqual(currentDirectory, resolvedWorkspaceRoot)) {
    if (await fileExists(path.join(currentDirectory, "composer.json"))) {
      return currentDirectory;
    }

    const parentDirectory = path.dirname(currentDirectory);
    if (parentDirectory === currentDirectory) {
      return undefined;
    }

    currentDirectory = parentDirectory;
  }

  return undefined;
}

export async function fileExists(filePath: string): Promise<boolean> {
  try {
    await fs.access(filePath);
    return true;
  } catch {
    return false;
  }
}

export function isInsideOrEqual(candidate: string, parent: string): boolean {
  const relativePath = path.relative(parent, candidate);

  return relativePath === "" || (!relativePath.startsWith("..") && !path.isAbsolute(relativePath));
}
