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

export type BinaryAvailabilityProbe = (binaryName: string, cwd: string) => Promise<boolean>;
export type ComposerGlobalBinDirectoryResolver = () => Promise<string | undefined>;
export type GlobalBinaryResolver = (binaryName: string, cwd: string) => Promise<string | undefined>;

export async function resolveElephactorBinary(
  composerRoot: string,
  configuredPath = configuredElephactorBinaryPath(),
  resolveGlobalBinary: GlobalBinaryResolver = resolveGlobalElephactorBinary,
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

  const localBinaryPath = path.join(composerRoot, "vendor", "bin", defaultElephactorBinaryName());
  const localBinaryInstalled = (await composerRequiresElephactor(composerRoot)) && (await fileExists(localBinaryPath));

  if (localBinaryInstalled) {
    return {
      binaryPath: localBinaryPath,
      configured: false,
      installed: true,
    };
  }

  const globalBinaryName = defaultElephactorBinaryName();
  const globalBinaryPath = await resolveGlobalBinary(globalBinaryName, composerRoot);

  return {
    binaryPath: globalBinaryPath ?? globalBinaryName,
    configured: false,
    installed: globalBinaryPath !== undefined,
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

export async function resolveGlobalElephactorBinary(
  binaryName: string,
  cwd: string,
  binaryAvailableOnPath: BinaryAvailabilityProbe = elephactorBinaryAvailableOnPath,
  resolveComposerGlobalBinDirectory: ComposerGlobalBinDirectoryResolver = composerGlobalBinDirectory,
): Promise<string | undefined> {
  if (await binaryAvailableOnPath(binaryName, cwd)) {
    return binaryName;
  }

  const binDirectory = await resolveComposerGlobalBinDirectory();
  if (binDirectory === undefined) {
    return undefined;
  }

  const binaryPath = path.join(binDirectory, binaryName);

  return await fileExists(binaryPath) ? binaryPath : undefined;
}

async function elephactorBinaryAvailableOnPath(binaryName: string, cwd: string): Promise<boolean> {
  return new Promise((resolve) => {
    const childProcess = spawn(binaryName, ["--version"], {
      cwd,
      shell: process.platform === "win32",
      stdio: "ignore",
    });

    childProcess.on("error", () => {
      resolve(false);
    });

    childProcess.on("close", (code) => {
      resolve(code === 0);
    });
  });
}

async function composerGlobalBinDirectory(): Promise<string | undefined> {
  return new Promise((resolve) => {
    let stdout = "";
    const childProcess = spawn("composer", ["global", "config", "bin-dir", "--absolute"], {
      shell: process.platform === "win32",
    });

    childProcess.stdout.on("data", (data: Buffer) => {
      stdout += data.toString();
    });

    childProcess.on("error", () => {
      resolve(undefined);
    });

    childProcess.on("close", (code) => {
      const binDirectory = parseComposerGlobalBinDirectoryOutput(stdout);
      resolve(code === 0 && binDirectory !== undefined ? binDirectory : undefined);
    });
  });
}

export function parseComposerGlobalBinDirectoryOutput(stdout: string): string | undefined {
  const outputLines = stdout
    .split(/\r?\n/)
    .map((line) => line.trim())
    .filter((line) => line !== "");

  return outputLines[outputLines.length - 1];
}

function configuredElephactorBinaryPath(): string {
  return vscode.workspace
    .getConfiguration(extensionConfigurationSection)
    .get<string>(binaryPathConfigurationName, "");
}
