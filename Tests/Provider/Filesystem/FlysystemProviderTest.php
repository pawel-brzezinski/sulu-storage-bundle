<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Provider\Filesystem;

use PB\Bundle\SuluStorageBundle\Provider\Filesystem\FlysystemProvider;
use PB\Bundle\SuluStorageBundle\Tests\Fake\Flysystem\FakeFilesystemInterface;
use PB\Bundle\SuluStorageBundle\Tests\Library\Utils\Reflection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class FlysystemProviderTest extends TestCase
{
    /** @var ObjectProphecy|FakeFilesystemInterface */
    private $fsMock;

    protected function setUp()
    {
        $this->fsMock = $this->prophesize(FakeFilesystemInterface::class);
    }

    protected function tearDown()
    {
        $this->fsMock = null;
    }

    public function testConstruct()
    {
        // Given
        $providerUnderTest = $this->buildProvider();

        // When
        $adapter = Reflection::getPropertyValue($providerUnderTest, 'filesystem');

        // Then
        $this->assertSame($this->fsMock->reveal(), $adapter);
    }

    public function testExists()
    {
        // Given
        $path = '/foo/bar/file.jpeg';
        $expected = true;

        // Mock FilesystemInterface::has()
        $this->fsMock->has($path)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildProvider()->exists($path);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function writeDataProvider()
    {
        $path = '/uploads';
        $content = 'file-content';
        $config = ['foo' => 'bar'];

        return [
            'write with default config parameter' => [$path, $content, [], null],
            'write with custom config parameter' => [$path, $content, $config, $config],
        ];
    }

    /**
     * @dataProvider writeDataProvider
     *
     * @param string $expectedPath
     * @param string $expectedContent
     * @param array $expectedConfig
     * @param mixed|null $config
     *
     * @throws
     */
    public function testWrite($expectedPath, $expectedContent, $expectedConfig, $config = null)
    {
        // Given
        $expected = true;

        // Mock FilesystemInterface::write()
        $this->fsMock
            ->write($expectedPath, $expectedContent, $expectedConfig)
            ->shouldBeCalledTimes(1)
            ->willReturn($expected)
        ;
        // End

        $providerUnderTest = $this->buildProvider();

        // When

        // If expectedConfig is null then we assume that we call write method with default config parameter.
        if (null === $config) {
            $actual = $providerUnderTest->write($expectedPath, $expectedContent);
        } else {
            $actual = $providerUnderTest->write($expectedPath, $expectedContent, $config);
        }

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testLoad()
    {
        // Given
        $path = '/example.jpeg';
        $expected = 'file-content';

        // Mock FilesystemInterface::read()
        $this->fsMock->read($path)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildProvider()->read($path);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testMove()
    {
        // Given
        $source = '/foo/bar/file.jpeg';
        $dest = '/lorem/ipsum/file.jpeg';

        // Mock FilesystemInterface::rename()
        $this->fsMock->rename($source, $dest)->shouldBeCalledTimes(1)->willReturn(true);
        // End

        // When
        $actual = $this->buildProvider()->move($source, $dest);

        // Then
        $this->assertTrue($actual);
    }

    public function testDelete()
    {
        // Given
        $expected = true;
        $path = '/foo/bar/example.jpeg';

        // Mock FilesystemInterface::delete()
        $this->fsMock->delete($path)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildProvider()->delete($path);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testDeleteDir()
    {
        // Given
        $expected = true;
        $path = '/foo/bar';

        // Mock FilesystemInterface::deleteDir()
        $this->fsMock->deleteDir($path)->shouldBeCalledTimes(1)->willReturn($expected);
        // End

        // When
        $actual = $this->buildProvider()->deleteDir($path);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testGetPathToFileContent()
    {
        // Given
        $path = '/path/to/file.jpeg';
        $contentPath = '/content'.$path;

        // Mock FilesystemInterface::getPathToFileContent()
        $this->fsMock->getPathToFileContent($path)->shouldBeCalledTimes(1)->willReturn($contentPath);
        // End

        // When
        $actual = $this->buildProvider()->getPathToFileContent($path);

        // Then
        $this->assertSame($contentPath, $actual);
    }

    /**
     * @return FlysystemProvider
     */
    private function buildProvider()
    {
        /** @var FakeFilesystemInterface $fs */
        $fs = $this->fsMock->reveal();

        return new FlysystemProvider($fs);
    }
}
