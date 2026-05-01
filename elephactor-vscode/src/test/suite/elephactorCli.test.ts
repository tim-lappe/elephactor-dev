import * as assert from "node:assert";
import * as fs from "node:fs/promises";
import * as os from "node:os";
import * as path from "node:path";
import { defaultElephactorBinaryName, elephactorComposerPackage } from "../../constants";
import { composerRequiresElephactor, resolveElephactorBinary } from "../../elephactorCli";

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
      await resolveElephactorBinary(composerRoot, ""),
      {
        binaryPath,
        configured: false,
        installed: true,
      },
    );
  });

  test("requires the Composer package for default binary installation", async () => {
    const composerRoot = await createComposerProject({});
    const binaryPath = path.join(composerRoot, "vendor", "bin", defaultElephactorBinaryName());

    await fs.mkdir(path.dirname(binaryPath), { recursive: true });
    await fs.writeFile(binaryPath, "");

    assert.deepStrictEqual(
      await resolveElephactorBinary(composerRoot, ""),
      {
        binaryPath,
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
      await resolveElephactorBinary(composerRoot, "tools/elephactor"),
      {
        binaryPath,
        configured: true,
        installed: true,
      },
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
});
