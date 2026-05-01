import * as assert from "node:assert";
import * as fs from "node:fs/promises";
import * as path from "node:path";
import * as vscode from "vscode";

suite("Elephactor Extension", () => {
  test("registers install command", async () => {
    const commands = await vscode.commands.getCommands(true);
    assert.ok(
      commands.includes("vscode-elephactor.installElephactor"),
      "Command vscode-elephactor.installElephactor is missing",
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
      packageJson.contributes?.configuration?.properties?.["vscode-elephactor.binaryPath"],
      "Configuration vscode-elephactor.binaryPath is missing",
    );
  });
});

