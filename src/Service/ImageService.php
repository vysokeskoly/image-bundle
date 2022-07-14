<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use VysokeSkoly\ImageBundle\Entity\ImageInterface;
use VysokeSkoly\ImageBundle\Exception\NotFoundException;
use VysokeSkoly\ImageBundle\Storage\StorageInterface;

class ImageService
{
    public const IMAGE_CODE_CACHED = 304;
    public const IMAGE_CODE_NOT_CACHED = 200;

    private ImageRepositoryInterface $imageRepository;

    private StorageInterface $storage;

    private ImageGenerator $imageGenerator;

    public function __construct(
        ImageRepositoryInterface $imageRepository,
        StorageInterface $storage,
        ImageGenerator $imageGenerator
    ) {
        $this->imageRepository = $imageRepository;
        $this->storage = $storage;
        $this->imageGenerator = $imageGenerator;
    }

    public function addFormat(string $name, array $config): void
    {
        $this->imageGenerator->addFormat($name, $config);
    }

    /**
     * @param int|string $key
     */
    public function getImage($key, string $slug, string $extension, ?string $format = null): ImageInterface
    {
        $image = $this->imageRepository->findByUrl($key, $slug, $extension);

        if (!$this->storage->exists($image->getKey())) {
            throw new NotFoundException();
        }

        $content = $this->storage->read($image->getKey());
        if ($format) {
            $content = $this->imageGenerator->generate($content, $extension, $format);
        }

        $image->setContent($content);

        return $image;
    }

    public function getImageStatusCode(Request $request, ImageInterface $image): int
    {
        if ($request->server->get('HTTP_IF_NONE_MATCH') === $image->getETag()) {
            return self::IMAGE_CODE_CACHED;
        }

        return self::IMAGE_CODE_NOT_CACHED;
    }
}
