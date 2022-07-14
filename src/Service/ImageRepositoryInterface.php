<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Service;

use VysokeSkoly\ImageBundle\Entity\ImageInterface;

interface ImageRepositoryInterface
{
    /**
     * @param int|string $key
     */
    public function findByUrl($key, string $slug, string $extension): ImageInterface;
}
