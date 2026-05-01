import * as vscode from "vscode";
import { spawn } from "node:child_process";
import * as fs from "node:fs/promises";
import * as path from "node:path";

const extensionConfiguration = "vscode-elephactor";
const installAction = "Install Elephactor";

type BinaryResolution = {
  binaryPath: string;
  configured: boolean;
  installed: boolean;
};

export function activate(context: vscode.ExtensionContext) {
  const outputChannel = vscode.window.createOutputChannel("Elephactor");

  const disposable = vscode.commands.registerCommand(
    "vscode-elephactor.installElephactor",
    async (uri?: vscode.Uri) => {
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
    },
  );
  context.subscriptions.push(disposable);

  const renameDisposable = vscode.workspace.onDidRenameFiles(async (event) => {
    for (const file of event.files) {
      await handleRenamedFile(file, outputChannel);
    }
  });

  context.subscriptions.push(renameDisposable, outputChannel);
}

export function deactivate() {}

async function handleRenamedFile(
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

  const oldDirectory = path.dirname(file.oldUri.fsPath);
  const newDirectory = path.dirname(file.newUri.fsPath);
  const oldClassName = path.basename(file.oldUri.fsPath, path.extname(file.oldUri.fsPath));
  const newClassName = path.basename(file.newUri.fsPath, path.extname(file.newUri.fsPath));
  const directoryChanged = oldDirectory !== newDirectory;
  const classNameChanged = oldClassName !== newClassName;

  if (!directoryChanged && !classNameChanged) {
    return;
  }

  const commandSets: string[][] = [];
  if (directoryChanged) {
    commandSets.push(["class:move", file.newUri.fsPath, newDirectory, "--skip-file-move"]);
  }

  if (classNameChanged) {
    commandSets.push(["class:rename", oldClassName, newClassName, "--skip-file-rename"]);
  }

  for (const args of commandSets) {
    const exitCode = await runElephactor(binary.binaryPath, composerRoot, args, outputChannel);
    if (exitCode !== 0) {
      vscode.window.showErrorMessage(`Elephactor failed while running ${args[0]}. See the Elephactor output for details.`);
      outputChannel.show(true);
      return;
    }
  }
}

export async function findComposerRoot(uri: vscode.Uri): Promise<string | undefined> {
  const workspaceFolder = vscode.workspace.getWorkspaceFolder(uri);
  if (workspaceFolder === undefined) {
    return undefined;
  }

  return findComposerRootFromDirectory(path.dirname(uri.fsPath), workspaceFolder.uri.fsPath);
}

export async function resolveElephactorBinary(composerRoot: string): Promise<BinaryResolution> {
  const configuredPath = vscode.workspace
    .getConfiguration(extensionConfiguration)
    .get<string>("binaryPath", "")
    .trim();

  if (configuredPath !== "") {
    const binaryPath = path.isAbsolute(configuredPath)
      ? configuredPath
      : path.join(composerRoot, configuredPath);

    return {
      binaryPath,
      configured: true,
      installed: await fileExists(binaryPath),
    };
  }

  const binaryPath = path.join(composerRoot, "vendor", "bin", process.platform === "win32" ? "elephactor.bat" : "elephactor");

  return {
    binaryPath,
    configured: false,
    installed: (await composerRequiresElephactor(composerRoot)) && (await fileExists(binaryPath)),
  };
}

async function resolveComposerRootForInstall(uri?: vscode.Uri): Promise<string | undefined> {
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

async function findComposerRootFromDirectory(startDirectory: string, workspaceRoot: string): Promise<string | undefined> {
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

async function handleMissingBinary(uri: vscode.Uri, binary: BinaryResolution): Promise<void> {
  if (binary.configured) {
    vscode.window.showErrorMessage(`Configured Elephactor binary does not exist: ${binary.binaryPath}`);
    return;
  }

  const selectedAction = await vscode.window.showWarningMessage(
    "Elephactor is not installed in this Composer project.",
    installAction,
  );

  if (selectedAction === installAction) {
    await vscode.commands.executeCommand("vscode-elephactor.installElephactor", uri);
  }
}

async function runElephactor(
  binaryPath: string,
  cwd: string,
  args: string[],
  outputChannel: vscode.OutputChannel,
): Promise<number> {
  outputChannel.appendLine(`$ ${binaryPath} ${args.join(" ")}`);

  return new Promise((resolve) => {
    const childProcess = spawn(binaryPath, args, { cwd });

    childProcess.stdout.on("data", (data: Buffer) => {
      outputChannel.append(data.toString());
    });

    childProcess.stderr.on("data", (data: Buffer) => {
      outputChannel.append(data.toString());
    });

    childProcess.on("error", (error) => {
      outputChannel.appendLine(error.message);
      resolve(1);
    });

    childProcess.on("close", (code) => {
      resolve(code ?? 1);
    });
  });
}

async function composerRequiresElephactor(composerRoot: string): Promise<boolean> {
  const composerJsonPath = path.join(composerRoot, "composer.json");
  const composerJson = JSON.parse(await fs.readFile(composerJsonPath, "utf8")) as {
    require?: Record<string, unknown>;
    "require-dev"?: Record<string, unknown>;
  };

  return composerJson.require?.["tim-lappe/elephactor"] !== undefined
    || composerJson["require-dev"]?.["tim-lappe/elephactor"] !== undefined;
}

async function fileExists(filePath: string): Promise<boolean> {
  try {
    await fs.access(filePath);
    return true;
  } catch {
    return false;
  }
}

function isInsideOrEqual(candidate: string, parent: string): boolean {
  const relativePath = path.relative(parent, candidate);

  return relativePath === "" || (!relativePath.startsWith("..") && !path.isAbsolute(relativePath));
}

function isPhpFile(uri: vscode.Uri): boolean {
  return uri.scheme === "file" && path.extname(uri.fsPath).toLowerCase() === ".php";
}
