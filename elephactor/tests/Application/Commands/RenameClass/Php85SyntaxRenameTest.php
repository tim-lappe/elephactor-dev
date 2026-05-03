<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Model\PhpVersion;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\ElephactorTests\Application\VirtualFile;

final class Php85SyntaxRenameTest extends ElephactorTestCase
{
    private VirtualFile $pipeUsage;

    protected function phpVersion(): PhpVersion
    {
        return PhpVersion::PHP_8_5;
    }

    public function setUp(): void
    {
        parent::setUp();

        $servicesDir = $this->sourceDirectory->createOrGetDirecotry('Services');
        $servicesDir->createFile('OldTransformer.php', <<<'PHP'
        <?php

        namespace VirtualTestNamespace\Services;

        final class OldTransformer
        {
            public static function accepts(string $message): bool
            {
                return $message !== '';
            }
        }
        PHP);

        $usageDir = $this->sourceDirectory->createOrGetDirecotry('Usage');
        $this->pipeUsage = $usageDir->createFile('PipeUsage.php', <<<'PHP'
        <?php

        namespace VirtualTestNamespace\Usage;

        use VirtualTestNamespace\Services\OldTransformer;

        final class PipeUsage
        {
            public function matches(string $message): bool
            {
                (void) $message;

                return $message
                    |> strtolower(...)
                    |> (static fn (string $normalized): bool => OldTransformer::accepts($normalized));
            }
        }
        PHP);

        $this->workspace->reloadIndices();
    }

    public function testRenamesReferencesInPhp85Expressions(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('OldTransformer')))
            ->first();
        if ($class === null) {
            self::fail('Class OldTransformer not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('NewTransformer')));

        $this->codeMatches($this->pipeUsage->content(), <<<'PHP'
        <?php

        namespace VirtualTestNamespace\Usage;

        use VirtualTestNamespace\Services\NewTransformer;

        final class PipeUsage
        {
            public function matches(string $message): bool
            {
                (void) $message;

                return $message
                    |> strtolower(...)
                    |> (static fn (string $normalized): bool => NewTransformer::accepts($normalized));
            }
        }
        PHP);
    }
}
