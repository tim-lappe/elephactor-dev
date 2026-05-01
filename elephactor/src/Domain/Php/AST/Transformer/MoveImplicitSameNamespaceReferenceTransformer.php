<?php

declare(strict_types=1);

namespace TimLappe\Elephactor\Domain\Php\AST\Transformer;

use TimLappe\Elephactor\Domain\Php\AST\Model\Expression\ConstantFetchExpressionNode;
use TimLappe\Elephactor\Domain\Php\AST\Model\Expression\FunctionCallExpressionNode;
use TimLappe\Elephactor\Domain\Php\AST\Model\Name\QualifiedNameNode;
use TimLappe\Elephactor\Domain\Php\AST\Model\Node;
use TimLappe\Elephactor\Domain\Php\AST\Model\Statement\NamespaceDefinitionNode;
use TimLappe\Elephactor\Domain\Php\AST\Model\Statement\UseStatementNode;
use TimLappe\Elephactor\Domain\Php\AST\Model\Value\FullyQualifiedName;
use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\AST\Model\Value\QualifiedName;
use TimLappe\Elephactor\Domain\Php\AST\Traversal\VisitorContext;
use TimLappe\Elephactor\Domain\Php\AST\Transformer\Refactorer\QualifiedNameChanger;

final class MoveImplicitSameNamespaceReferenceTransformer extends AbsractNodeTransformer
{
    private readonly QualifiedName $oldNamespace;

    public function __construct(
        private readonly QualifiedName $oldFullyQualifiedName,
        private readonly FullyQualifiedName $newFullyQualifiedName,
    ) {
        parent::__construct();

        $this->oldNamespace = $oldFullyQualifiedName->removeLastPart();
    }

    public function enter(Node $node, VisitorContext $context): void
    {
        if ($node instanceof NamespaceDefinitionNode) {
            $context->set('namespaceNode', $node);
        }

        if ($node instanceof UseStatementNode) {
            $context->set('insideUseStatement', true);

            $identifiers = $node->identifiersImported();
            $identifiersImported = $context->getArrayOf('identifiersImported', Identifier::class);
            $context->set('identifiersImported', [...$identifiersImported, ...$identifiers]);
        }

        if ($node instanceof FunctionCallExpressionNode && $node->callable() instanceof QualifiedNameNode) {
            $this->addSkippedQualifiedName($context, $node->callable());
        }

        if ($node instanceof ConstantFetchExpressionNode) {
            $this->addSkippedQualifiedName($context, $node->name());
        }

        if (!$node instanceof QualifiedNameNode) {
            return;
        }

        if ($context->getBoolean('insideUseStatement') === true) {
            return;
        }

        if (!$context->has('namespaceNode')) {
            return;
        }

        if ($node->qualifiedName() instanceof FullyQualifiedName) {
            return;
        }

        $namespaceNode = $context->get('namespaceNode', NamespaceDefinitionNode::class);
        if (!$namespaceNode->name()->qualifiedName()->equals($this->oldNamespace)) {
            return;
        }

        if ($namespaceNode->name() === $node) {
            return;
        }

        if (!$node->qualifiedName()->equals(new QualifiedName([$this->oldFullyQualifiedName->lastPart()]))) {
            return;
        }

        if ($this->importsIdentifier($this->oldFullyQualifiedName->lastPart(), $context->getArrayOf('identifiersImported', Identifier::class))) {
            return;
        }

        if ($node->qualifiedName()->isReservedTypeName()) {
            return;
        }

        if ($this->isSkippedQualifiedName($context, $node)) {
            return;
        }

        $this->refactorings->add(new QualifiedNameChanger($node, $this->newFullyQualifiedName));
    }

    public function leave(Node $node, VisitorContext $context): void
    {
        if ($node instanceof UseStatementNode && $context->has('insideUseStatement')) {
            $context->set('insideUseStatement', false);
        }
    }

    /**
     * @param list<Identifier> $identifiersImported
     */
    private function importsIdentifier(Identifier $identifier, array $identifiersImported): bool
    {
        foreach ($identifiersImported as $imported) {
            if ($imported->equals($identifier)) {
                return true;
            }
        }

        return false;
    }

    private function addSkippedQualifiedName(VisitorContext $context, QualifiedNameNode $node): void
    {
        $context->set('skippedQualifiedNames', [...$context->getArray('skippedQualifiedNames'), $node]);
    }

    private function isSkippedQualifiedName(VisitorContext $context, QualifiedNameNode $node): bool
    {
        foreach ($context->getArray('skippedQualifiedNames') as $skipped) {
            if ($skipped === $node) {
                return true;
            }
        }

        return false;
    }
}
