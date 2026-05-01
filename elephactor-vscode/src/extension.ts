import * as vscode from "vscode";
import { findComposerRoot } from "./composerProject";
import { resolveElephactorBinary } from "./elephactorCli";
import { registerInstallCommand } from "./installCommand";
import { registerPhpFileRenameHandler } from "./phpFileRename";

export { findComposerRoot, resolveElephactorBinary };

export function activate(context: vscode.ExtensionContext) {
  const outputChannel = vscode.window.createOutputChannel("Elephactor");

  context.subscriptions.push(
    registerInstallCommand(),
    registerPhpFileRenameHandler(outputChannel),
    outputChannel,
  );
}

export function deactivate() {}
