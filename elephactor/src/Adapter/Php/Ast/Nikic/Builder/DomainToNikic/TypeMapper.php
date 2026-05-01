<?php

declare(strict_types=1);

namespace TimLappe\Elephactor\Adapter\Php\Ast\Nikic\Builder\DomainToNikic;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use TimLappe\Elephactor\Domain\Php\AST\Model as Ast;
use TimLappe\Elephactor\Domain\Php\AST\Model\Type\NamedTypeNode;
use TimLappe\Elephactor\Domain\Php\AST\Model\Type\NullableTypeNode;
use TimLappe\Elephactor\Domain\Php\AST\Model\Type\SpecialTypeNode;
use TimLappe\Elephactor\Domain\Php\AST\Model\Type\UnionTypeNode;
use TimLappe\Elephactor\Domain\Php\AST\Model\Value\FullyQualifiedName;
use TimLappe\Elephactor\Domain\Php\AST\Model\Value\QualifiedName;

final class TypeMapper
{
    use AdapterNodeReuserTrait;

    public function buildType(?Ast\TypeNode $type): Identifier|Name|ComplexType|null
    {
        if ($type === null) {
            return null;
        }

        if ($type instanceof NullableTypeNode) {
            $inner = $this->buildType($type->inner());
            if (!$inner instanceof Identifier && !$inner instanceof Name) {
                throw new \RuntimeException('Nullable type requires an inner named type');
            }

            return $this->reuseAdapterNode($type, new NullableType($inner));
        }

        if ($type instanceof UnionTypeNode) {
            $types = array_map(
                function (Ast\TypeNode $node): Identifier|Name|IntersectionType {
                    $built = $this->buildType($node);
                    if ($built instanceof Identifier || $built instanceof Name || $built instanceof IntersectionType) {
                        return $built;
                    }

                    throw new \RuntimeException('Unsupported union type segment');
                },
                $type->types(),
            );

            return $this->reuseAdapterNode($type, new UnionType($types));
        }

        if ($type instanceof Ast\Type\IntersectionTypeNode) {
            $types = array_map(
                function (Ast\TypeNode $node): Identifier|Name {
                    $built = $this->buildType($node);
                    if ($built instanceof Identifier || $built instanceof Name) {
                        return $built;
                    }

                    throw new \RuntimeException('Unsupported intersection type segment');
                },
                $type->types(),
            );

            return $this->reuseAdapterNode($type, new IntersectionType($types));
        }

        if ($type instanceof NamedTypeNode) {
            $built = $this->buildQualifiedName($type->name()->qualifiedName());

            return $this->reuseAdapterNode($type, $built);
        }

        if ($type instanceof SpecialTypeNode) {
            return $this->reuseAdapterNode($type, new Identifier($type->type()->value));
        }

        throw new \RuntimeException('Unsupported type node: ' . $type::class);
    }

    public function buildEnumScalarType(?Ast\TypeNode $type): ?Identifier
    {
        $scalarType = $this->buildType($type);
        if ($scalarType === null) {
            return null;
        }

        if (!$scalarType instanceof Identifier) {
            throw new \RuntimeException('Enum scalar type must be string or int');
        }

        return $scalarType;
    }

    public function buildQualifiedName(QualifiedName $name): Name
    {
        $parts = array_map(
            static fn (Ast\Value\Identifier $identifier): string => $identifier->value(),
            $name->parts(),
        );

        if ($name instanceof FullyQualifiedName) {
            return new Name\FullyQualified($parts);
        }

        return new Name($parts);
    }
}
