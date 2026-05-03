import * as assert from "node:assert";
import * as fs from "node:fs/promises";
import * as os from "node:os";
import * as path from "node:path";
import { defaultElephactorBinaryName, elephactorComposerPackage } from "../../constants";
import {
  composerRequiresElephactor,
  parseComposerGlobalBinDirectoryOutput,
  resolveElephactorBinary,
  resolveGlobalElephactorBinary,
} from "../../elephactorCli";

suite("Elephactor CLI resolution", () => {
  const tempDirectories: string[] = [];

  teardown(async () => {
    await Promise.all(tempDirectories.map((directory) => fs.rm(directory, { recursive: true, force: true })));
    tempDirectories.length = 0;
  });

  test("resolves an installed default Composer binary", async () => {
    const composerRoot = await createComposerProject({ "require-dev": { [elephactorComposerPackage]: "*" } });
    const binaryPath = path.join(composerRoot, "vendor", "bin", defaultElephactorBinaryName());

    await fs.mkdir(path.dirname(binaryPath), { recursive: true });
    await fs.writeFile(binaryPath, "");

    assert.deepStrictEqual(
      await resolveElephactorBinary(composerRoot, "", missingGlobalBinary),
      {
        binaryPath,
        configured: false,
        installed: true,
      },
    );
  });

  test("falls back to a missing global binary when the local Composer package is absent", async () => {
    const composerRoot = await createComposerProject({});
    const localBinaryPath = path.join(composerRoot, "vendor", "bin", defaultElephactorBinaryName());

    await fs.mkdir(path.dirname(localBinaryPath), { recursive: true });
    await fs.writeFile(localBinaryPath, "");

    assert.deepStrictEqual(
      await resolveElephactorBinary(composerRoot, "", missingGlobalBinary),
      {
        binaryPath: defaultElephactorBinaryName(),
        configured: false,
        installed: false,
      },
    );
  });

  test("resolves configured relative binary paths from the Composer root", async () => {
    const composerRoot = await createComposerProject({});
    const binaryPath = path.join(composerRoot, "tools", "elephactor");

    await fs.mkdir(path.dirname(binaryPath), { recursive: true });
    await fs.writeFile(binaryPath, "");

    assert.deepStrictEqual(
      await resolveElephactorBinary(composerRoot, "tools/elephactor", missingGlobalBinary),
      {
        binaryPath,
        configured: true,
        installed: true,
      },
    );
  });

  test("resolves a global Elephactor binary on PATH", async () => {
    const composerRoot = await createComposerProject({});

    assert.deepStrictEqual(
      await resolveElephactorBinary(composerRoot, "", globalBinaryOnPath),
      {
        binaryPath: defaultElephactorBinaryName(),
        configured: false,
        installed: true,
      },
    );
  });

  test("prefers the local Composer binary over a global binary", async () => {
    const composerRoot = await createComposerProject({ "require-dev": { [elephactorComposerPackage]: "*" } });
    const binaryPath = path.join(composerRoot, "vendor", "bin", defaultElephactorBinaryName());

    await fs.mkdir(path.dirname(binaryPath), { recursive: true });
    await fs.writeFile(binaryPath, "");

    assert.deepStrictEqual(
      await resolveElephactorBinary(composerRoot, "", globalBinaryOnPath),
      {
        binaryPath,
        configured: false,
        installed: true,
      },
    );
  });

  test("prefers configured paths over a global binary", async () => {
    const composerRoot = await createComposerProject({});
    const binaryPath = path.join(composerRoot, "tools", "elephactor");

    assert.deepStrictEqual(
      await resolveElephactorBinary(composerRoot, "tools/elephactor", globalBinaryOnPath),
      {
        binaryPath,
        configured: true,
        installed: false,
      },
    );
  });

  test("resolves a global Composer bin directory when the binary is not on PATH", async () => {
    const globalBinDirectory = await createTempDirectory();
    const binaryPath = path.join(globalBinDirectory, defaultElephactorBinaryName());

    await fs.writeFile(binaryPath, "");

    assert.strictEqual(
      await resolveGlobalElephactorBinary(defaultElephactorBinaryName(), globalBinDirectory, unavailableOnPath, async () => globalBinDirectory),
      binaryPath,
    );
  });

  test("parses the Composer global bin directory when warnings precede it", () => {
    assert.strictEqual(
      parseComposerGlobalBinDirectoryOutput([
        "Deprecated: Composer warning",
        "/Users/example/.composer/vendor/bin",
        "",
      ].join("\n")),
      "/Users/example/.composer/vendor/bin",
    );
  });

  test("detects Elephactor in require and require-dev", async () => {
    const requiredRoot = await createComposerProject({ require: { [elephactorComposerPackage]: "*" } });
    const devRequiredRoot = await createComposerProject({ "require-dev": { [elephactorComposerPackage]: "*" } });

    assert.strictEqual(await composerRequiresElephactor(requiredRoot), true);
    assert.strictEqual(await composerRequiresElephactor(devRequiredRoot), true);
  });

  async function createComposerProject(composerJson: object): Promise<string> {
    const directory = await fs.mkdtemp(path.join(os.tmpdir(), "elephactor-vscode-"));
    tempDirectories.push(directory);
    await fs.writeFile(path.join(directory, "composer.json"), JSON.stringify(composerJson));

    return directory;
  }

  async function createTempDirectory(): Promise<string> {
    const directory = await fs.mkdtemp(path.join(os.tmpdir(), "elephactor-vscode-"));
    tempDirectories.push(directory);

    return directory;
  }

  async function globalBinaryOnPath(binaryName: string): Promise<string> {
    return binaryName;
  }

  async function unavailableOnPath(): Promise<boolean> {
    return false;
  }

  async function missingGlobalBinary(): Promise<undefined> {
    return undefined;
  }
});
