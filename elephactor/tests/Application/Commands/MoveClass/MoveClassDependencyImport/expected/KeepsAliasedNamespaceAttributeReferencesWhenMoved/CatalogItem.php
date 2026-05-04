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
