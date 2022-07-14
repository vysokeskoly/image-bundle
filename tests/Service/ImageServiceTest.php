<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Service;

use Assert\InvalidArgumentException;
use Mockery as m;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ServerBag;
use VysokeSkoly\ImageBundle\AbstractTestCase;
use VysokeSkoly\ImageBundle\Entity\ImageInterface;
use VysokeSkoly\ImageBundle\Exception\InvalidImageException;
use VysokeSkoly\ImageBundle\Exception\NotFoundException;
use VysokeSkoly\ImageBundle\Storage\StorageInterface;

class ImageServiceTest extends AbstractTestCase
{
    private ImageService $imageService;
    /** @var ImageRepositoryInterface|m\MockInterface */
    private $imageRepository;
    /** @var StorageInterface|m\MockInterface */
    private $storage;
    /** @var ImageGenerator|m\MockInterface */
    private $imageGenerator;

    protected function setUp(): void
    {
        $this->imageRepository = m::mock(ImageRepositoryInterface::class);
        $this->storage = m::mock(StorageInterface::class);
        $this->imageGenerator = m::mock(ImageGenerator::class);

        $this->imageService = new ImageService($this->imageRepository, $this->storage, $this->imageGenerator);
    }

    public function testShouldLoadImageWithThumbnail(): void
    {
        $format = 'test';

        $this->imageRepository->shouldReceive('findByUrl')
            ->once()
            ->andReturnUsing(function ($id, $slug, $extension) {
                $this->assertEquals($id, 123);
                $this->assertEquals($slug, 'this-is-slug');
                $this->assertEquals($extension, 'ext');

                $image = $this->createImageMock();

                $image->shouldReceive('getKey')
                    ->twice()
                    ->andReturn('this-is-image-key');

                $image->shouldReceive('setContent')
                    ->once()
                    ->andReturnUsing(function ($content): void {
                        $this->assertEquals('this-is-thumbnail', $content);
                    });

                return $image;
            });

        $this->storage->shouldReceive('exists')
            ->once()
            ->with('this-is-image-key')
            ->andReturn(true);

        $this->storage->shouldReceive('read')
            ->once()
            ->with('this-is-image-key')
            ->andReturn('original-image-content');

        $this->imageGenerator->shouldReceive('generate')
            ->once()
            ->with('original-image-content', 'ext', $format)
            ->andReturn('this-is-thumbnail');

        $this->imageService->getImage(123, 'this-is-slug', 'ext', $format);
    }

    public function testShouldNotFindImageInDatabase(): void
    {
        $this->expectException(NotFoundException::class);

        $this->imageRepository->shouldReceive('findByUrl')
            ->once()
            ->andReturnUsing(function ($id, $slug, $extension): void {
                $this->assertEquals($id, 123);
                $this->assertEquals($slug, 'this-is-slug');
                $this->assertEquals($extension, 'ext');

                throw new NotFoundException();
            });

        $this->imageService->getImage(123, 'this-is-slug', 'ext');
    }

    public function testShouldNotFindImageInStorage(): void
    {
        $this->expectException(NotFoundException::class);

        $this->imageRepository->shouldReceive('findByUrl')
            ->once()
            ->andReturnUsing(function ($id, $slug, $extension) {
                $this->assertEquals($id, 123);
                $this->assertEquals($slug, 'this-is-slug');
                $this->assertEquals($extension, 'ext');

                $image = $this->createImageMock();

                $image->shouldReceive('getKey')
                    ->once()
                    ->andReturn('this-is-image-key');

                $image->shouldReceive('setContent')
                    ->never();

                return $image;
            });

        $this->storage->shouldReceive('exists')
            ->once()
            ->andReturnUsing(function ($key) {
                $this->assertEquals('this-is-image-key', $key);

                return false;
            });

        $this->storage->shouldReceive('read')
            ->never();

        $this->imageService->getImage(123, 'this-is-slug', 'ext');
    }

    public function testShouldFailDueUnknownFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->imageRepository->shouldReceive('findByUrl')
            ->once()
            ->andReturnUsing(function ($id, $slug, $extension) {
                $this->assertEquals($id, 123);
                $this->assertEquals($slug, 'this-is-slug');
                $this->assertEquals($extension, 'ext');

                $image = $this->createImageMock();

                $image->shouldReceive('getKey')
                    ->twice()
                    ->andReturn('this-is-image-key');

                $image->shouldReceive('setContent')
                    ->never();

                return $image;
            });

        $this->storage->shouldReceive('exists')
            ->once()
            ->andReturnUsing(function ($key) {
                $this->assertEquals('this-is-image-key', $key);

                return true;
            });

        $this->storage->shouldReceive('read')
            ->once()
            ->andReturnUsing(function ($key) {
                $this->assertEquals('this-is-image-key', $key);

                return 'original-image-content';
            });

        $this->imageGenerator->shouldReceive('generate')
            ->once()
            ->with('original-image-content', 'ext', 'this format is bad')
            ->andThrow(m::mock(InvalidArgumentException::class));

        $this->imageService->getImage(123, 'this-is-slug', 'ext', 'this format is bad');
    }

    /**
     * @dataProvider statusProvider
     */
    public function testShouldGetStatusCode(?string $serverHeader, string $imageETag, int $code): void
    {
        $server = m::mock(ServerBag::class);
        $server->shouldReceive('get')
            ->once()
            ->andReturnUsing(function ($key) use ($serverHeader) {
                $this->assertEquals('HTTP_IF_NONE_MATCH', $key);

                return $serverHeader;
            });

        /** @var Request|m\MockInterface $request */
        $request = m::mock(Request::class);
        $request->server = $server;

        $image = $this->createImageMock();
        $image->shouldReceive('getETag')
            ->once()
            ->andReturn($imageETag);

        $statusCode = $this->imageService->getImageStatusCode($request, $image);

        $this->assertEquals($code, $statusCode);
    }

    public function statusProvider(): array
    {
        return [
            'CACHED' => ['sadsadln547NFKJWRN80dsad', 'sadsadln547NFKJWRN80dsad', 304],
            'NOT CACHED #1' => ['im not cached', 'sadsadln547NFKJWRN80dsad', 200],
            'NOT CACHED #2' => [null, 'sadsadln547NFKJWRN80dsad', 200],
        ];
    }

    public function testShouldNotGetStatusDueEmptyImage(): void
    {
        $this->expectException(InvalidImageException::class);

        $server = m::mock(ServerBag::class);
        $server->shouldReceive('get')
            ->once()
            ->andReturnUsing(function ($key) {
                $this->assertEquals('HTTP_IF_NONE_MATCH', $key);

                return 'last modified';
            });

        /** @var Request|m\MockInterface $request */
        $request = m::mock(Request::class);
        $request->server = $server;

        $image = $this->createImageMock();
        $image->shouldReceive('getId')
            ->once()
            ->andReturn(123);
        $image->shouldReceive('getETag')
            ->once()
            ->andThrow(InvalidImageException::emptyContent($image));

        $this->imageService->getImageStatusCode($request, $image);
    }

    /**
     * @return ImageInterface|m\MockInterface
     */
    private function createImageMock()
    {
        return m::mock(ImageInterface::class);
    }

    public function testShouldAddMoreFormats(): void
    {
        $this->imageGenerator->shouldReceive('addFormat')
            ->once()
            ->with('dynamic', ['options']);

        $this->imageService->addFormat('dynamic', ['options']);
    }
}
