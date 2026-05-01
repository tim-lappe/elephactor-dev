<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\MoveClass;

use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Psr4\Model\Psr4ClassFile;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\MoveFile;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\ElephactorTests\Application\VirtualDirectory;
use TimLappe\ElephactorTests\Application\VirtualFile;
use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class MoveClassDependencyImportTest extends ElephactorTestCase
{
    public function testKeepsImportedDependenciesAndGlobalFunctionsWhenMoved(): void
    {
        $applicationNamespace = $this->sourceDirectory
            ->createOrGetDirecotry('Printer')
            ->createOrGetDirecotry('Application');

        $applicationNamespace
            ->createOrGetDirecotry('Dto')
            ->createFile('ResponseDto.php', <<<'PHP'
            <?php

            namespace VirtualTestNamespace\Printer\Application\Dto;

            final readonly class ResponseDto
            {
                public function __construct(public string $name)
                {
                }
            }
            PHP);

        $this->sourceDirectory
            ->createOrGetDirecotry('Printer')
            ->createOrGetDirecotry('Domain')
            ->createOrGetDirecotry('Entity')
            ->createFile('PrintedLabel.php', <<<'PHP'
            <?php

            namespace VirtualTestNamespace\Printer\Domain\Entity;

            final class PrintedLabel
            {
                public function name(): string
                {
                    return 'label';
                }
            }
            PHP);

        $this->sourceDirectory
            ->createOrGetDirecotry('Printer')
            ->createOrGetDirecotry('Domain')
            ->createOrGetDirecotry('Exception')
            ->createFile('JobFailedException.php', <<<'PHP'
            <?php

            namespace VirtualTestNamespace\Printer\Domain\Exception;

            final class JobFailedException extends \RuntimeException
            {
            }
            PHP);

        $this->sourceDirectory
            ->createOrGetDirecotry('Printer')
            ->createOrGetDirecotry('Domain')
            ->createOrGetDirecotry('Repository')
            ->createFile('LabelRepository.php', <<<'PHP'
            <?php

            namespace VirtualTestNamespace\Printer\Domain\Repository;

            final class LabelRepository
            {
            }
            PHP);

        $this->sourceDirectory
            ->createOrGetDirecotry('Framework')
            ->createOrGetDirecotry('Exception')
            ->createFile('BadRequestException.php', <<<'PHP'
            <?php

            namespace VirtualTestNamespace\Framework\Exception;

            final class BadRequestException extends \RuntimeException
            {
            }
            PHP);

        $applicationNamespace->createFile('MovedApplicationService.php', <<<'PHP'
        <?php

        namespace VirtualTestNamespace\Printer\Application;

        use VirtualTestNamespace\Framework\Exception\BadRequestException;
        use VirtualTestNamespace\Printer\Application\Dto\ResponseDto;
        use VirtualTestNamespace\Printer\Domain\Entity\PrintedLabel;
        use VirtualTestNamespace\Printer\Domain\Exception\JobFailedException;
        use VirtualTestNamespace\Printer\Domain\Repository\LabelRepository;

        final readonly class MovedApplicationService
        {
            public function __construct(private LabelRepository $labels)
            {
            }

            /**
             * @return list<ResponseDto>
             */
            public function listLabels(): array
            {
                return array_map(
                    fn (PrintedLabel $label): ResponseDto => new ResponseDto($label->name()),
                    $this->labels->recent(),
                );
            }

            public function resend(): void
            {
                try {
                    $this->labels->resend();
                } catch (JobFailedException $e) {
                    throw new BadRequestException($e->getMessage(), $e);
                }
            }
        }
        PHP);

        $targetNamespace = $applicationNamespace->createOrGetDirecotry('Dto');

        $this->workspace->reloadIndices();

        $this->moveClass('MovedApplicationService', $targetNamespace);

        $movedFile = $this->findFileIn($targetNamespace, 'MovedApplicationService.php');
        self::assertNotNull($movedFile);

        self::assertSame(<<<'PHP'
        <?php

        namespace VirtualTestNamespace\Printer\Application\Dto;

        use VirtualTestNamespace\Framework\Exception\BadRequestException;
        use VirtualTestNamespace\Printer\Application\Dto\ResponseDto;
        use VirtualTestNamespace\Printer\Domain\Entity\PrintedLabel;
        use VirtualTestNamespace\Printer\Domain\Exception\JobFailedException;
        use VirtualTestNamespace\Printer\Domain\Repository\LabelRepository;

        final readonly class MovedApplicationService
        {
            public function __construct(private LabelRepository $labels)
            {
            }

            /**
             * @return list<ResponseDto>
             */
            public function listLabels(): array
            {
                return array_map(
                    fn (PrintedLabel $label): ResponseDto => new ResponseDto($label->name()),
                    $this->labels->recent(),
                );
            }

            public function resend(): void
            {
                try {
                    $this->labels->resend();
                } catch (JobFailedException $e) {
                    throw new BadRequestException($e->getMessage(), $e);
                }
            }
        }
        PHP, $movedFile->content());
    }

    public function testKeepsReferencesToSameNamespaceDependencies(): void
    {
        $sharedNamespace = $this->sourceDirectory
            ->createOrGetDirecotry('Domain')
            ->createOrGetDirecotry('Shared');

        $sharedNamespace->createFile('DependencyClass.php', <<<'PHP'
        <?php

        namespace VirtualTestNamespace\Domain\Shared;

        class DependencyClass
        {
        }
        PHP);

        $classFile = $sharedNamespace->createFile('MovedClass.php', <<<'PHP'
        <?php

        namespace VirtualTestNamespace\Domain\Shared;

        class MovedClass
        {
            public function __construct(private DependencyClass $dependency)
            {
            }

            public function dependency(): DependencyClass
            {
                return $this->dependency;
            }
        }
        PHP);

        $targetNamespace = $this->sourceDirectory
            ->createOrGetDirecotry('Domain')
            ->createOrGetDirecotry('Target');

        $this->workspace->reloadIndices();

        $this->moveClass('MovedClass', $targetNamespace);

        $movedFile = $this->findFileIn($targetNamespace, 'MovedClass.php');
        self::assertNotNull($movedFile);

        $this->codeMatches($movedFile->content(), <<<'PHP'
        <?php

        namespace VirtualTestNamespace\Domain\Target;

        class MovedClass
        {
            public function __construct(private \VirtualTestNamespace\Domain\Shared\DependencyClass $dependency)
            {
            }

            public function dependency(): \VirtualTestNamespace\Domain\Shared\DependencyClass
            {
                return $this->dependency;
            }
        }
        PHP);
    }

    public function testKeepsAliasedNamespaceAttributeReferencesWhenMoved(): void
    {
        $this->sourceDirectory
            ->createOrGetDirecotry('Catalog')
            ->createOrGetDirecotry('Domain')
            ->createOrGetDirecotry('Repository')
            ->createFile('CatalogItemRepository.php', <<<'PHP'
            <?php

            namespace VirtualTestNamespace\Catalog\Domain\Repository;

            final class CatalogItemRepository
            {
            }
            PHP);

        $entityNamespace = $this->sourceDirectory
            ->createOrGetDirecotry('Catalog')
            ->createOrGetDirecotry('Domain')
            ->createOrGetDirecotry('Entity');

        $catalogItemImageFile = $entityNamespace->createFile('CatalogItemImage.php', <<<'PHP'
        <?php

        namespace VirtualTestNamespace\Catalog\Domain\Entity;

        use Doctrine\ORM\Mapping as ORM;

        #[ORM\Entity]
        final class CatalogItemImage
        {
            #[ORM\OneToOne(targetEntity: CatalogItem::class, inversedBy: 'catalogItemImage')]
            private CatalogItem $catalogItem;

            public function __construct(CatalogItem $catalogItem)
            {
                $this->catalogItem = $catalogItem;
            }
        }
        PHP);

        $entityNamespace->createFile('CatalogItem.php', <<<'PHP'
        <?php

        namespace VirtualTestNamespace\Catalog\Domain\Entity;

        use Doctrine\ORM\Mapping as ORM;
        use VirtualTestNamespace\Catalog\Domain\Repository\CatalogItemRepository;

        #[ORM\Entity(repositoryClass: CatalogItemRepository::class)]
        #[ORM\Table(name: 'item_type')]
        final class CatalogItem
        {
            #[ORM\Id]
            #[ORM\Column(type: 'catalog_item_id', unique: true)]
            private string $catalogItemId;

            #[ORM\OneToOne(targetEntity: CatalogItemImage::class)]
            private ?CatalogItemImage $catalogItemImage = null;
        }
        PHP);

        $targetNamespace = $this->sourceDirectory
            ->createOrGetDirecotry('Catalog')
            ->createOrGetDirecotry('Application')
            ->createOrGetDirecotry('Controller');

        $this->workspace->reloadIndices();

        $this->moveClass('CatalogItem', $targetNamespace);

        $movedFile = $this->findFileIn($targetNamespace, 'CatalogItem.php');
        self::assertNotNull($movedFile);

        $this->codeMatches($movedFile->content(), <<<'PHP'
        <?php

        namespace VirtualTestNamespace\Catalog\Application\Controller;

        use Doctrine\ORM\Mapping as ORM;
        use VirtualTestNamespace\Catalog\Domain\Repository\CatalogItemRepository;

        #[ORM\Entity(repositoryClass: CatalogItemRepository::class)]
        #[ORM\Table(name: 'item_type')]
        final class CatalogItem
        {
            #[ORM\Id]
            #[ORM\Column(type: 'catalog_item_id', unique: true)]
            private string $catalogItemId;

            #[ORM\OneToOne(targetEntity: \VirtualTestNamespace\Catalog\Domain\Entity\CatalogItemImage::class)]
            private ?\VirtualTestNamespace\Catalog\Domain\Entity\CatalogItemImage $catalogItemImage = null;
        }
        PHP);

        $this->codeMatches($catalogItemImageFile->content(), <<<'PHP'
        <?php

        namespace VirtualTestNamespace\Catalog\Domain\Entity;

        use Doctrine\ORM\Mapping as ORM;

        #[ORM\Entity]
        final class CatalogItemImage
        {
            #[ORM\OneToOne(targetEntity: \VirtualTestNamespace\Catalog\Application\Controller\CatalogItem::class, inversedBy: 'catalogItemImage')]
            private \VirtualTestNamespace\Catalog\Application\Controller\CatalogItem $catalogItem;

            public function __construct(\VirtualTestNamespace\Catalog\Application\Controller\CatalogItem $catalogItem)
            {
                $this->catalogItem = $catalogItem;
            }
        }
        PHP);
    }

    private function moveClass(string $className, VirtualDirectory $targetDirectory): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier($className)))->first();
        if ($class === null) {
            self::fail(sprintf('Class %s not found in workspace', $className));
        }

        if (!$class instanceof Psr4ClassFile) {
            self::fail(sprintf('Class %s is not a Psr4ClassFile', $className));
        }

        $this->application
            ->refactoringExecutor()
            ->handle(new MoveFile($class->file(), $targetDirectory));
    }

    private function findFileIn(VirtualDirectory $directory, string $fileName): ?File
    {
        return $directory
            ->childFiles()
            ->first(static fn (VirtualFile $file): bool => $file->name() === $fileName);
    }
}
