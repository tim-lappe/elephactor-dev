import { spawn } from "node:child_process";
import * as fs from "node:fs/promises";
import * as path from "node:path";
import * as vscode from "vscode";
import {
  binaryPathConfigurationName,
  defaultElephactorBinaryName,
  elephactorComposerPackage,
  extensionConfigurationSection,
} from "./constants";
import { fileExists } from "./composerProject";

export type BinaryResolution = {
  binaryPath: string;
  configured: boolean;
  installed: boolean;
};

export async function resolveElephactorBinary(
  composerRoot: string,
  configuredPath = configuredElephactorBinaryPath(),
): Promise<BinaryResolution> {
  const normalizedConfiguredPath = configuredPath.trim();

  if (normalizedConfiguredPath !== "") {
    const binaryPath = path.isAbsolute(normalizedConfiguredPath)
      ? normalizedConfiguredPath
      : path.join(composerRoot, normalizedConfiguredPath);

    return {
      binaryPath,
      configured: true,
      installed: await fileExists(binaryPath),
    };
  }

  const binaryPath = path.join(composerRoot, "vendor", "bin", defaultElephactorBinaryName());

  return {
    binaryPath,
    configured: false,
    installed: (await composerRequiresElephactor(composerRoot)) && (await fileExists(binaryPath)),
  };
}

export async function runElephactor(
  binaryPath: string,
  cwd: string,
  args: string[],
  outputChannel: Pick<vscode.OutputChannel, "append" | "appendLine">,
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

export async function composerRequiresElephactor(composerRoot: string): Promise<boolean> {
  const composerJsonPath = path.join(composerRoot, "composer.json");
  const composerJson = JSON.parse(await fs.readFile(composerJsonPath, "utf8")) as {
    require?: Record<string, unknown>;
    "require-dev"?: Record<string, unknown>;
  };

  return composerJson.require?.[elephactorComposerPackage] !== undefined
    || composerJson["require-dev"]?.[elephactorComposerPackage] !== undefined;
}

function configuredElephactorBinaryPath(): string {
  return vscode.workspace
    .getConfiguration(extensionConfigurationSection)
    .get<string>(binaryPathConfigurationName, "");
}
