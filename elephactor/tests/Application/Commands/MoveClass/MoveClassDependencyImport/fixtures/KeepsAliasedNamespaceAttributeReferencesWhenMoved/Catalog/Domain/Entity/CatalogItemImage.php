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
