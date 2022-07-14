<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Service;

use Imagine\Gmagick\Imagine;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\PointInterface;
use Mockery as m;
use VysokeSkoly\ImageBundle\AbstractTestCase;

/**
 * @group unit
 */
class ImageGeneratorTest extends AbstractTestCase
{
    private ImageGenerator $imageGenerator;
    /** @var ImageContentParser|m\MockInterface */
    private $imageContentParser;
    /** @var Imagine|m\MockInterface */
    private $imagine;

    protected function setUp(): void
    {
        $this->imagine = m::mock(Imagine::class);
        $this->imageContentParser = m::mock(ImageContentParser::class);
        $this->imageContentParser->shouldReceive('parseRealImageType')->andReturnNull();

        $formats = [
            'test' => [
                'width' => 100,
                'height' => 100,
            ],
            'another format' => [
                'width' => 222,
                'height' => 333,
            ],
        ];

        $this->imageGenerator = new ImageGenerator(
            $this->imagine,
            new CropConfigParser(),
            $this->imageContentParser,
            $formats
        );
    }

    /**
     * @dataProvider formatProvider
     */
    public function testShouldGenerateImageThumbnail(string $format, int $width, int $height): void
    {
        $thumbnail = $this->createThumbnailMock($width, $height);

        $this->imagine->shouldReceive('load')
            ->once()
            ->with('original-image-content')
            ->andReturn($thumbnail);

        $this->assertSame(
            'this-is-thumbnail',
            $this->imageGenerator->generate('original-image-content', 'ext', $format)
        );
    }

    public function formatProvider(): array
    {
        return [
            // name, width, height
            ['test', 100, 100],
            ['another format', 222, 333],
        ];
    }

    /**
     * @return ImageInterface|m\MockInterface
     */
    private function createThumbnailMock(int $width, int $height)
    {
        /** @var ImageInterface|m\MockInterface $thumbnail */
        $thumbnail = m::mock(ImageInterface::class);

        $thumbnail->shouldReceive('thumbnail')
            ->once()
            ->andReturnUsing(function (BoxInterface $size, $mode) use ($width, $height, $thumbnail) {
                $this->assertEquals($width, $size->getWidth());
                $this->assertEquals($height, $size->getHeight());
                $this->assertEquals(ManipulatorInterface::THUMBNAIL_OUTBOUND, $mode);

                return $thumbnail;
            });

        $thumbnail->shouldReceive('get')
            ->once()
            ->andReturnUsing(function ($extension) {
                $this->assertEquals('ext', $extension);

                return 'this-is-thumbnail';
            });

        return $thumbnail;
    }

    public function testShouldGenerateCroppedThumbnail(): void
    {
        /*
         * total image size is more than 500 x 300 (w x h)
         * cropped part is 400 x 200 (w x h)
         * thumbnail = resized cropped part is 40 x 20 (w x h)
         */

        $this->imageGenerator->addFormat(
            'dynamic',
            [
                'height' => 20,
                'width' => 40,
                'crop' => [
                    'x' => 100,
                    'y' => 100,
                    'x2' => 300,
                    'y2' => 500,
                ],
            ]
        );

        $thumbnail = $this->createThumbnailMock(40, 20);

        $thumbnail->shouldReceive('crop')
            ->once()
            ->andReturnUsing(function (PointInterface $point, BoxInterface $size) use ($thumbnail) {
                $this->assertSame(100, $point->getX());
                $this->assertSame(100, $point->getY());

                $this->assertSame(200, $size->getWidth());
                $this->assertSame(400, $size->getHeight());

                return $thumbnail;
            });

        $this->imagine->shouldReceive('load')
            ->once()
            ->with('original-image-content')
            ->andReturn($thumbnail);

        $this->assertSame(
            'this-is-thumbnail',
            $this->imageGenerator->generate('original-image-content', 'ext', 'dynamic')
        );
    }
}
