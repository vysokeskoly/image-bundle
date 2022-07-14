<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Service;

use VysokeSkoly\ImageBundle\Entity\ImageInterface;

interface ImageRepositoryInterface
{
    public function findByUrl(int|string $key, string $slug, string $extension): ImageInterface;
}
