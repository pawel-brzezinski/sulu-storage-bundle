<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Media\FormatCache;

use PB\Bundle\SuluStorageBundle\Media\FormatCache\PBFormatCache;
use PB\Bundle\SuluStorageBundle\Media\Resolver\FileResolverInterface;
use PB\Bundle\SuluStorageBundle\Provider\Filesystem\FilesystemProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\MediaBundle\Media\Exception\ImageProxyMediaNotFoundException;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class PBFormatCacheTest extends TestCase
{
    const DEFAULT_PROXY_PATH = '/uploads/media/{slug}';
    const DEFAULT_SEGMENTS = 15;
    const DEFAULT_FORMATS = ['sulu-400x400' => ['key' => 'sulu-400x400'], 'sulu-260x' => ['key' => 'sulu-260x']];

    /** @var ObjectProphecy|FilesystemProviderInterface */
    private $fspMock;

    /** @var ObjectProphecy|FileResolverInterface */
    private $frMock;

    protected function setUp()
    {
        $this->fspMock = $this->prophesize(FilesystemProviderInterface::class);
        $this->frMock = $this->prophesize(FileResolverInterface::class);
    }

    protected function tearDown()
    {
        $this->fspMock = null;
        $this->frMock = null;
    }

    public function testGetFilesystemProvider()
    {
        // When
        $actual = $this->buildFormatCache()->getFilesystemProvider();

        // Then
        $this->assertSame($this->fspMock->reveal(), $actual);
    }

    public function loadDataProvider()
    {
        return [
            'file exists' => ['/01', true, 'file-content', 1, 'example.jpeg', '', 'image-format'],
            'file not exists' => ['/09', false, null, 879, 'example.jpeg', '', 'image-format'],
        ];
    }

    /**
     * @dataProvider loadDataProvider
     *
     * @param string $expectedSegmentPath
     * @param bool $expectedExistFlag
     * @param string|null $expectedContent
     * @param string|int $id
     * @param string $fileName
     * @param string $options
     * @param string $format
     *
     * @throws \Exception
     */
    public function testShouldLoadAndReturnImageContentWhenFilesystemProviderWillNotThrowAnException(
        $expectedSegmentPath,
        $expectedExistFlag,
        $expectedContent,
        $id,
        $fileName,
        $options,
        $format
    ) {
        // Given

        // Mock FileResolverInterface::resolveFileName()
        $this->frMock->resolveFileName($fileName)->shouldBeCalledTimes(1)->willReturn($fileName);
        // End

        // Mock FileResolverInterface::resolveFormatFilePath()
        $expectedFilePath = $format.'/'.ltrim($expectedSegmentPath, '/').'/'.$fileName;
        $this->frMock
            ->resolveFormatFilePath($expectedSegmentPath, $format, $fileName)
            ->shouldBeCalledTimes(1)
            ->willReturn($expectedFilePath);
        ;
        // End

        // Mock FilesystemProviderInterface::exists()
        $this->fspMock->exists($expectedFilePath)->shouldBeCalledTimes(1)->willReturn($expectedExistFlag);
        // End

        // Mock FilesystemProviderInterface::read()
        if (false !== $expectedExistFlag) {
            $this->fspMock->read($expectedFilePath)->shouldBeCalledTimes(1)->willReturn($expectedContent);
        } else {
            $this->fspMock->read(Argument::any())->shouldNotBeCalled();
        }
        // End

        // When
        $actual = $this->buildFormatCache()->load($id, $fileName, $options, $format);

        // Then
        $this->assertSame($expectedContent, $actual);
    }

    public function testShouldLoadAndThrowImageProxyMediaNotFoundExceptionWhenFilesystemProviderWillNotThrowAnException()
    {
        // Expect
        $fileName = 'example.jpeg';
        $format = 'image-format';
        $expectedSegmentPath = '/01';
        $expectedFilePath = $format.'/'.ltrim($expectedSegmentPath, '/').'/'.$fileName;

        $this->expectException(ImageProxyMediaNotFoundException::class);
        $this->expectExceptionMessage('Format media at path "'.$expectedFilePath.'" not found');

        // Given
        $id = 1;

        // Mock FileResolverInterface::resolveFileName()
        $this->frMock->resolveFileName($fileName)->shouldBeCalledTimes(1)->willReturn($fileName);
        // End

        // Mock FileResolverInterface::resolveFormatFilePath()
        $this->frMock
            ->resolveFormatFilePath($expectedSegmentPath, $format, $fileName)
            ->shouldBeCalledTimes(1)
            ->willReturn($expectedFilePath);
        ;
        // End

        // Mock FilesystemProviderInterface::exists()
        $this->fspMock->exists($expectedFilePath)->shouldBeCalledTimes(1)->willReturn(true);
        // End

        // Mock FilesystemProviderInterface::read()
        $this->fspMock->read($expectedFilePath)->shouldBeCalledTimes(1)->willThrow(new \Exception('Test exception'));
        // End

        // When
        $this->buildFormatCache()->load($id, $fileName, '', $format);
    }

    public function testShouldSaveAndReturnTrueWhenFilesystemProviderWillNotThrowAnException()
    {
        // Given
        $content = 'file-content';
        $id = 5;
        $fileName = 'example.jpeg';
        $options = [];
        $format = 'format-1';

        $formatFilePath = '/format-1/05/example.jpeg';

        // Mock FileResolverInterface::resolveFileName()
        $this->frMock->resolveFileName($fileName)->shouldBeCalledTimes(1)->willReturn($fileName);
        // End

        // Mock FileResolverInterface::resolveFilePath()
        $this->frMock->resolveFormatFilePath('/05', $format, $fileName)->shouldBeCalledTimes(1)->willReturn($formatFilePath);
        // End

        // Mock FilesystemProviderInterface::write()
        $this->fspMock->write($formatFilePath, $content)->shouldBeCalledTimes(1);
        // End

        // When
        $actual = $this->buildFormatCache()->save($content, $id, $fileName, $options, $format);

        // Then
        $this->assertTrue($actual);
    }

    public function testShouldSaveAndReturnFalseWhenFilesystemProviderThrowAnException()
    {
        // Given
        $content = 'file-content';
        $id = 5;
        $fileName = 'example.jpeg';
        $options = [];
        $format = 'format-1';

        $formatFilePath = '/format-1/05/example.jpeg';

        // Mock FileResolverInterface::resolveFileName()
        $this->frMock->resolveFileName($fileName)->shouldBeCalledTimes(1)->willReturn($fileName);
        // End

        // Mock FileResolverInterface::resolveFilePath()
        $this->frMock->resolveFormatFilePath('/05', $format, $fileName)->shouldBeCalledTimes(1)->willReturn($formatFilePath);
        // End

        // Mock FilesystemProviderInterface::write()
        $this->fspMock->write($formatFilePath, $content)->shouldBeCalledTimes(1)->willThrow(new \Exception());
        // End

        // When
        $actual = $this->buildFormatCache()->save($content, $id, $fileName, $options, $format);

        // Then
        $this->assertFalse($actual);
    }

    public function testShouldPurgeAllFormatFilesForImage()
    {
        // Given
        $id = 1;
        $fileName = 'example.jpeg';
        $options = json_encode(['fileName' => $fileName]);

        // Mock FileResolverInterface::resolveFileName()
        $this->frMock->resolveFileName($fileName)->shouldBeCalledTimes(1)->willReturn($fileName);
        // End

        foreach (self::DEFAULT_FORMATS as $format) {
            $filePath = '/'.$format['key'].'/01/'.$fileName;

            // Mock FileResolverInterface::resolveFilePath()
            $this->frMock->resolveFormatFilePath('/01', $format['key'], $fileName)->shouldBeCalledTimes(1)->willReturn($filePath);
            // End

            // Mock FilesystemProviderInterface::remove()
            $this->fspMock->delete($filePath)->shouldBeCalledTimes(1);
            // End
        }

        // When
        $actual = $this->buildFormatCache()->purge($id, $fileName, $options);

        // Then
        $this->assertTrue($actual);
    }

    public function testShouldPurgeAndReturnFalseWhenFilesystemProviderThrowAnException()
    {
        // Given
        $id = 1;
        $fileName = 'example.jpeg';
        $options = json_encode(['fileName' => $fileName]);

        // Mock FileResolverInterface::resolveFileName()
        $this->frMock->resolveFileName($fileName)->shouldBeCalledTimes(1)->willReturn($fileName);
        // End

        foreach (self::DEFAULT_FORMATS as $format) {
            $filePath = '/'.$format['key'].'/01/'.$fileName;

            // Mock FileResolverInterface::resolveFilePath()
            $this->frMock->resolveFormatFilePath('/01', $format['key'], $fileName)->shouldBeCalledTimes(1)->willReturn($filePath);
            // End

            // Mock FilesystemProviderInterface::remove()
            $this->fspMock->delete($filePath)->shouldBeCalledTimes(1)->willThrow(new \Exception('Test exception'));
            // End
        }

        // When
        $actual = $this->buildFormatCache()->purge($id, $fileName, $options);

        // Then
        $this->assertTrue($actual);
    }

    public function testShouldClearFormatFiles()
    {
        // Given

        // Mock FilesystemProviderInterface::deleteDir()
        foreach (self::DEFAULT_FORMATS as $format) {
            $path = '/'.$format['key'];
            $this->fspMock->deleteDir($path)->shouldBeCalledTimes(1);
        }
        // End

        // When
        $this->buildFormatCache()->clear();
    }

    public function testShouldCallClearFormatFilesAndNotThrowExceptionWhenFilesystemProviderThrowAnException()
    {
        // Given

        // Mock FilesystemProviderInterface::deleteDir()
        foreach (self::DEFAULT_FORMATS as $format) {
            $path = '/'.$format['key'];
            $this->fspMock->deleteDir($path)->shouldBeCalledTimes(1)->willThrow(new \Exception('Test error'));
        }
        // End

        // When
        $this->buildFormatCache()->clear();
    }

    /**
     * @return PBFormatCache
     */
    private function buildFormatCache()
    {
        return new PBFormatCache(
            $this->fspMock->reveal(),
            $this->frMock->reveal(),
            self::DEFAULT_PROXY_PATH,
            self::DEFAULT_SEGMENTS,
            self::DEFAULT_FORMATS
        );
    }
}
