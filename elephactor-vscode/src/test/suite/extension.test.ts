import * as assert from "node:assert";
import * as fs from "node:fs/promises";
import * as path from "node:path";
import * as vscode from "vscode";
import { binaryPathConfigurationKey, commandIds } from "../../constants";

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
});

