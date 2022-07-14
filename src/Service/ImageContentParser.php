<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Service;

class ImageContentParser
{
    /**
     * @see http://stackoverflow.com/questions/29644168/get-image-file-type-programmatically-in-swift
     * @see http://stackoverflow.com/questions/885597/string-to-byte-array-in-php
     */
    public function parseRealImageType(string $content): ?string
    {
        $bytes = unpack('C*', mb_substr($content, 0, 1));

        if (!is_array($bytes)) {
            return null;
        }

        $imageTypeByte = array_shift($bytes);

        switch ($imageTypeByte) {
            case 0xFF:
                return 'jpg';
            case 0x89:
                return 'png';
            case 0x47:
                return 'gif';
        }

        return null;
    }
}
