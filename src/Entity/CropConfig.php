<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Entity;

use Imagine\Image\BoxInterface;
use Imagine\Image\PointInterface;

class CropConfig
{
    private bool $shouldCrop;
    private ?PointInterface $start;
    private ?BoxInterface $size;

    public function shouldCrop(): bool
    {
        return $this->shouldCrop && $this->getStart() !== null && $this->getSize() !== null;
    }

    public function setShouldCrop(bool $shouldCrop): void
    {
        $this->shouldCrop = $shouldCrop;
    }

    public function getStart(): ?PointInterface
    {
        return $this->start;
    }

    public function setStart(PointInterface $start): void
    {
        $this->start = $start;
    }

    public function getSize(): ?BoxInterface
    {
        return $this->size;
    }

    public function setSize(BoxInterface $size): void
    {
        $this->size = $size;
    }
}
