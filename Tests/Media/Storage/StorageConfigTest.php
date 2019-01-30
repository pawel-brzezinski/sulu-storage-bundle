<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Media\Storage;

use PB\Bundle\SuluStorageBundle\Media\Storage\StorageConfig;
use PHPUnit\Framework\TestCase;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class StorageConfigTest extends TestCase
{
    /** @var StorageConfig */
    private $configUnderTest;

    protected function setUp()
    {
        $this->configUnderTest = new StorageConfig();
    }

    protected function tearDown()
    {
        $this->configUnderTest = null;
    }

    public function segmentDataProvider()
    {
        return [
            'segment as integer' => [123, 123],
            'segment as string' => [123, '123'],
        ];
    }

    /**
     * @dataProvider segmentDataProvider
     *
     * @param $expected
     * @param $segment
     */
    public function testSetAndGetSegment($expected, $segment)
    {
        // When
        $actualSetter = $this->configUnderTest->setSegment($segment);
        $actualGetter = $this->configUnderTest->getSegment();

        // Then
        $this->assertSame($this->configUnderTest, $actualSetter);
        $this->assertSame($expected, $actualGetter);
    }

    public function testSetAndGetFileName()
    {
        // Given
        $expected = 'fileName.jpeg';

        // When
        $actualSetter = $this->configUnderTest->setFileName($expected);
        $actualGetter = $this->configUnderTest->getFileName();

        // Then
        $this->assertSame($this->configUnderTest, $actualSetter);
        $this->assertSame($expected, $actualGetter);
    }

    public function testSetAndGetContentPath()
    {
        // Given
        $expected = '/path/to/file/content/file.jpeg';

        // When
        $actualSetter = $this->configUnderTest->setContentPath($expected);
        $actualGetter = $this->configUnderTest->getContentPath();

        // Then
        $this->assertSame($this->configUnderTest, $actualSetter);
        $this->assertSame($expected, $actualGetter);
    }
}
