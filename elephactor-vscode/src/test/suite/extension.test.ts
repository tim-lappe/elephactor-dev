import * as assert from "node:assert";
import * as fs from "node:fs/promises";
import * as path from "node:path";
import * as vscode from "vscode";
import { binaryPathConfigurationKey, commandIds, elephactorComposerPackage } from "../../constants";
import { composerInstallCommand } from "../../installCommand";

suite("Elephactor Extension", () => {
  test("registers install command", async () => {
    const commands = await vscode.commands.getCommands(true);
    assert.ok(
      commands.includes(commandIds.installElephactor),
      `Command ${commandIds.installElephactor} is missing`,
    );
  });

  test("contributes binary path configuration", async () => {
    const packageJsonPath = path.resolve(__dirname, "..", "..", "..", "package.json");
    const packageJson = JSON.parse(await fs.readFile(packageJsonPath, "utf8")) as {
      contributes?: {
        configuration?: {
          properties?: Record<string, unknown>;
        };
      };
    };

    assert.ok(
      packageJson.contributes?.configuration?.properties?.[binaryPathConfigurationKey],
      `Configuration ${binaryPathConfigurationKey} is missing`,
    );
  });

  test("builds Composer commands for both install targets", () => {
    assert.strictEqual(
      composerInstallCommand("global"),
      `composer global require ${elephactorComposerPackage}:@dev`,
    );
    assert.strictEqual(
      composerInstallCommand("project"),
      `composer require --dev ${elephactorComposerPackage}`,
    );
  });
});

