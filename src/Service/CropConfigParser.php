<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Service;

use Assert\Assertion;
use Imagine\Image\Box;
use Imagine\Image\Point;
use VysokeSkoly\ImageBundle\Entity\CropConfig;

class CropConfigParser
{
    public function parseConfig(array $config): CropConfig
    {
        $cropConfig = new CropConfig();
        $cropConfig->setShouldCrop(false);

        if (array_key_exists('crop', $config)) {
            $crop = $config['crop'];

            if (empty($crop['x']) || empty($crop['y'])) {
                return $cropConfig;
            }

            $start = new Point($crop['x'], $crop['y']);

            if (!empty($crop['width']) && !empty($crop['height'])) {
                Assertion::keyNotExists($crop, 'x2');
                Assertion::keyNotExists($crop, 'y2');

                $size = new Box($crop['width'], $crop['height']);
            } elseif (!empty($crop['x2']) && !empty($crop['y2'])) {
                $width = $crop['x2'] - $start->getX();
                $height = $crop['y2'] - $start->getY();

                $size = new Box($width, $height);
            } else {
                return $cropConfig;
            }

            $cropConfig->setShouldCrop(true);
            $cropConfig->setStart($start);
            $cropConfig->setSize($size);
        }

        return $cropConfig;
    }
}
