import * as assert from "node:assert";
import * as path from "node:path";
import { buildRenameCommandSets } from "../../phpFileRename";

suite("PHP file rename handling", () => {
  test("builds no commands when neither directory nor class name changed", () => {
    const filePath = path.join("workspace", "src", "Example.php");

    assert.deepStrictEqual(buildRenameCommandSets(filePath, filePath), []);
  });

  test("builds a move command when the directory changed", () => {
    const oldFilePath = path.join("workspace", "src", "Example.php");
    const newFilePath = path.join("workspace", "src", "Domain", "Example.php");

    assert.deepStrictEqual(
      buildRenameCommandSets(oldFilePath, newFilePath),
      [["class:move", newFilePath, path.dirname(newFilePath), "--skip-file-move"]],
    );
  });

  test("builds a rename command when the class file name changed", () => {
    const oldFilePath = path.join("workspace", "src", "OldName.php");
    const newFilePath = path.join("workspace", "src", "NewName.php");

    assert.deepStrictEqual(
      buildRenameCommandSets(oldFilePath, newFilePath),
      [["class:rename", "OldName", "NewName", "--skip-file-rename"]],
    );
  });

  test("builds move before rename when directory and class file name changed", () => {
    const oldFilePath = path.join("workspace", "src", "OldName.php");
    const newFilePath = path.join("workspace", "src", "Domain", "NewName.php");

    assert.deepStrictEqual(
      buildRenameCommandSets(oldFilePath, newFilePath),
      [
        ["class:move", newFilePath, path.dirname(newFilePath), "--skip-file-move"],
        ["class:rename", "OldName", "NewName", "--skip-file-rename"],
      ],
    );
  });
});
