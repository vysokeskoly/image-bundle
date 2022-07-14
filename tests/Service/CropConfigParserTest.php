<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Service;

use Assert\InvalidArgumentException;
use Imagine\Image\Box;
use Imagine\Image\Point;
use VysokeSkoly\ImageBundle\AbstractTestCase;
use VysokeSkoly\ImageBundle\Entity\CropConfig;

/**
 * @group unit
 */
class CropConfigParserTest extends AbstractTestCase
{
    private CropConfigParser $cropConfigParser;

    protected function setUp(): void
    {
        $this->cropConfigParser = new CropConfigParser();
    }

    public function testShouldParseCropConfigFromEmptyFormatConfig(): void
    {
        $formatConfig = [];
        $expected = new CropConfig();
        $expected->setShouldCrop(false);

        $this->assertEquals($expected, $this->cropConfigParser->parseConfig($formatConfig));
    }

    /**
     * @dataProvider cropConfigProvider
     */
    public function testShouldParseCropConfigFromFormatConfig(
        array $cropConfig,
        int $x,
        int $y,
        int $width,
        int $height
    ): void {
        $formatConfig = [
            'crop' => $cropConfig,
        ];

        $expected = new CropConfig();
        $expected->setShouldCrop(true);
        $expected->setStart(new Point($x, $y));
        $expected->setSize(new Box($width, $height));

        $this->assertEquals($expected, $this->cropConfigParser->parseConfig($formatConfig));
    }

    public function cropConfigProvider(): array
    {
        return [
            'with x2, y2' => [
                'cropConfig' => [
                    'x' => 20,
                    'y' => 20,
                    'x2' => 80,
                    'y2' => 180,
                ],
                'x' => 20,
                'y' => 20,
                'width' => 60,
                'height' => 160,
            ],
            'with width and height' => [
                'cropConfig' => [
                    'x' => 20,
                    'y' => 20,
                    'width' => 60,
                    'height' => 160,
                ],
                'x' => 20,
                'y' => 20,
                'width' => 60,
                'height' => 160,
            ],
        ];
    }

    /**
     * @dataProvider invalidCropConfigProvider
     */
    public function testShouldParseCropConfigFromInvalidFormatConfig(array $cropConfig): void
    {
        $formatConfig = [
            'crop' => $cropConfig,
        ];

        $expected = new CropConfig();
        $expected->setShouldCrop(false);

        $this->assertEquals($expected, $this->cropConfigParser->parseConfig($formatConfig));
    }

    public function invalidCropConfigProvider(): array
    {
        return [
            'missing x' => [
                'cropConfig' => [
                    'y' => 20,
                    'x2' => 80,
                    'y2' => 180,
                ],
            ],
            'missing y' => [
                'cropConfig' => [
                    'x' => 20,
                    'width' => 80,
                    'height' => 180,
                ],
            ],
            'missing x2' => [
                'cropConfig' => [
                    'x' => 20,
                    'y' => 20,
                    'y2' => 180,
                ],
            ],
            'missing y2' => [
                'cropConfig' => [
                    'x' => 20,
                    'y' => 20,
                    'x2' => 80,
                ],
            ],
            'missing width' => [
                'cropConfig' => [
                    'x' => 20,
                    'y' => 20,
                    'height' => 160,
                ],
            ],
            'missing height' => [
                'cropConfig' => [
                    'x' => 20,
                    'y' => 20,
                    'width' => 160,
                ],
            ],
        ];
    }

    public function testShouldNotCreateCropConfigWithUnclearInput(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $formatConfig = [
            'crop' => [
                'x' => 20,
                'y' => 20,
                'width' => 60,
                'height' => 160,
                'x2' => 20,
                'y2' => 20,
            ],
        ];

        $this->cropConfigParser->parseConfig($formatConfig);
    }
}
