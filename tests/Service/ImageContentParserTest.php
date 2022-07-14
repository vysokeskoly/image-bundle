<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Service;

use VysokeSkoly\ImageBundle\AbstractTestCase;

/**
 * @group unit
 */
class ImageContentParserTest extends AbstractTestCase
{
    private ImageContentParser $imageParser;

    protected function setUp(): void
    {
        $this->imageParser = new ImageContentParser();
    }

    /**
     * @dataProvider contentProvider
     */
    public function testShouldParseRealImageType(string $content, ?string $expected): void
    {
        $this->assertSame($expected, $this->imageParser->parseRealImageType($content));
    }

    public function contentProvider(): array
    {
        return [
            // content, realExtension
            'empty' => ['', null],
            'invalid' => ['string', null],
            'jpg' => [$this->createContent('image.jpg'), 'jpg'],
            'png' => [$this->createContent('image.png'), 'png'],
            'gif' => [$this->createContent('image.gif'), 'gif'],
        ];
    }

    private function createContent(string $imageName): string
    {
        $imagePath = __DIR__ . '/../Fixtures/' . $imageName;

        return (string) file_get_contents($imagePath);
    }
}
