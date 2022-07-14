<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Exception;

use VysokeSkoly\ImageBundle\Entity\ImageInterface;

class InvalidImageException extends \Exception
{
    public static function emptyContent(ImageInterface $image): self
    {
        return new self(
            'Cannot get eTag of image #' . $image->getId() . ' without content. ' .
            'You have to set content first.'
        );
    }
}
