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
