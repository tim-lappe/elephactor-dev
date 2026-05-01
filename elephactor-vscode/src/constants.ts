export const extensionConfigurationSection = "vscode-elephactor";
export const binaryPathConfigurationName = "binaryPath";
export const binaryPathConfigurationKey = `${extensionConfigurationSection}.${binaryPathConfigurationName}`;

export const commandIds = {
  installElephactor: "vscode-elephactor.installElephactor",
} as const;

export const elephactorComposerPackage = "tim-lappe/elephactor";
export const installAction = "Install Elephactor";

export function defaultElephactorBinaryName(): string {
  return process.platform === "win32" ? "elephactor.bat" : "elephactor";
}
