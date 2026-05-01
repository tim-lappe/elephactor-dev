import * as assert from "node:assert";
import * as fs from "node:fs/promises";
import * as os from "node:os";
import * as path from "node:path";
import { findComposerRootFromDirectory, isInsideOrEqual } from "../../composerProject";

suite("Composer project discovery", () => {
  const tempDirectories: string[] = [];

  teardown(async () => {
    await Promise.all(tempDirectories.map((directory) => fs.rm(directory, { recursive: true, force: true })));
    tempDirectories.length = 0;
  });

  test("finds the nearest composer root within the workspace", async () => {
    const workspaceRoot = await createTempDirectory();
    const packageRoot = path.join(workspaceRoot, "packages", "app");
    const sourceDirectory = path.join(packageRoot, "src");

    await fs.mkdir(sourceDirectory, { recursive: true });
    await fs.writeFile(path.join(workspaceRoot, "composer.json"), "{}");
    await fs.writeFile(path.join(packageRoot, "composer.json"), "{}");

    assert.strictEqual(
      await findComposerRootFromDirectory(sourceDirectory, workspaceRoot),
      packageRoot,
    );
  });

  test("does not walk outside the workspace root", async () => {
    const repositoryRoot = await createTempDirectory();
    const workspaceRoot = path.join(repositoryRoot, "workspace");
    const sourceDirectory = path.join(workspaceRoot, "src");

    await fs.mkdir(sourceDirectory, { recursive: true });
    await fs.writeFile(path.join(repositoryRoot, "composer.json"), "{}");

    assert.strictEqual(
      await findComposerRootFromDirectory(sourceDirectory, workspaceRoot),
      undefined,
    );
  });

  test("checks whether a path is inside or equal to a parent path", async () => {
    const workspaceRoot = await createTempDirectory();

    assert.strictEqual(isInsideOrEqual(workspaceRoot, workspaceRoot), true);
    assert.strictEqual(isInsideOrEqual(path.join(workspaceRoot, "src"), workspaceRoot), true);
    assert.strictEqual(isInsideOrEqual(path.dirname(workspaceRoot), workspaceRoot), false);
  });

  async function createTempDirectory(): Promise<string> {
    const directory = await fs.mkdtemp(path.join(os.tmpdir(), "elephactor-vscode-"));
    tempDirectories.push(directory);

    return directory;
  }
});
