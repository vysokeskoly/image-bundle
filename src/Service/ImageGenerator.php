<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Service;

use Assert\Assertion;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageGenerator
{
    public const JPEG_QUALITY = 90;
    public const PNG_COMPRESSION_LEVEL = 9; // 0 - 9
    public const PNG_COMPRESSION_FILTER = 7; // 0 - 9

    public function __construct(
        private ImagineInterface $imagine,
        private CropConfigParser $cropConfigParser,
        private ImageContentParser $contentParser,
        private array $formats,
    ) {
    }

    public function addFormat(string $name, array $config): void
    {
        $this->formats[$name] = $this->buildConfigResolver()->resolve($config);
    }

    private function buildConfigResolver(): OptionsResolver
    {
        return (new OptionsResolver())
            ->setRequired('width')
            ->addAllowedTypes('width', 'int')
            ->setRequired('height')
            ->addAllowedTypes('height', 'int')
            ->setDefined('crop')
            ->addAllowedTypes('crop', 'array');
    }

    public function generate(string $content, string $extension, string $format): string
    {
        Assertion::keyExists($this->formats, $format);

        $config = $this->formats[$format];
        $cropConfig = $this->cropConfigParser->parseConfig($config);

        $image = $this->imagine->load($content);

        if ($cropConfig->shouldCrop()) {
            Assertion::notNull($cropConfig->getStart());
            Assertion::notNull($cropConfig->getSize());
            $image = $image->crop($cropConfig->getStart(), $cropConfig->getSize());
        }

        /** @var ImageInterface $thumbnail */
        $thumbnail = $image->thumbnail(
            new Box($config['width'], $config['height']),
            ImageInterface::THUMBNAIL_OUTBOUND,
        );

        return $thumbnail->get(
            $this->contentParser->parseRealImageType($content) ?: $extension,
            [
                'jpeg_quality' => self::JPEG_QUALITY,
                'png_compression_level' => self::PNG_COMPRESSION_LEVEL,
                'png_compression_filter' => self::PNG_COMPRESSION_FILTER,
            ],
        );
    }
}
