<?php

namespace PB\Bundle\SuluStorageBundle\Tests\FormatCache;

use PB\Bundle\SuluStorageBundle\FormatCache\PBFormatCache;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;
use Sulu\Bundle\MediaBundle\Media\Exception\ImageProxyInvalidUrl;
use Sulu\Bundle\MediaBundle\Media\Exception\ImageProxyUrlNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;

class PBFormatCacheTest extends AbstractTests
{
    protected $formats = [
        '400x400' => [
            'key' => '400x400',
            'internal' => true,
            'meta' => [
                'title' => [
                    'en' => '400x400 format',
                ],
            ],
            'scale' => [
                'x' => 400,
                'y' => 400,
                'mode' => 'outbound',
                'retina' => false,
                'forceRatio' => true,
            ],
            'transformations' => [],
            'options' => [],
        ],
    ];

    protected $proxyPath = '/uploads/media/{slug}';

    protected $storageOptions = [
        'segment' => '01',
        'fileName' => 'photo.jpeg',
    ];
    
    public function testSave()
    {
        $formatCache = $this->generateFormatCacheWithWriteSuccess();

        $result = $formatCache->save(
            file_get_contents(__DIR__ . '/../app/Resources/images/01/photo.jpeg'),
            1,
            'photo.jpeg',
            json_encode($this->storageOptions),
            '400x400'
        );

        $this->assertTrue($result);
    }

    public function testSaveException()
    {
        $formatCache = $this->generateFormatCacheWithWriteException();

        $result = $formatCache->save(
            file_get_contents(__DIR__ . '/../app/Resources/images/01/photo.jpeg'),
            1,
            'photo.jpeg',
            json_encode($this->storageOptions),
            '400x400'
        );

        $this->assertFalse($result);
    }

    public function testPurge()
    {
        $formatCache = $this->generateFormatCacheWithHasSuccess();

        $result = $formatCache->purge(1, 'photo.jpeg', json_encode($this->storageOptions));

        $this->assertTrue($result);
    }

    public function testGetMediaUrlIfFileNotExist()
    {
        $filesystemMock = $this->generateFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $managerMock = $this->generateStorageManagerMock();
        $managerMock
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($filesystemMock);
        $managerMock
            ->expects($this->once())
            ->method('getFormatFilePath')
            ->willReturn('400x400/01/1-photo.jpeg');

        $formatCache = new PBFormatCache($managerMock, $this->formats, $this->proxyPath);
        $result = $formatCache->getMediaUrl(
            1,
            'photo.jpeg',
            json_encode($this->storageOptions),
            '400x400',
            1,
            0
        );

        $this->assertEquals('/uploads/media/400x400/01/1-photo.jpeg?v=1-0', $result);
    }

    public function testGetMediaUrlIfFileExistAndHasExtUrl()
    {
        $filesystemMock = $this->generateFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $managerMock = $this->generateStorageManagerMock();
        $managerMock
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($filesystemMock);
        $managerMock
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn('http://example.com/photo.jpeg');

        $formatCache = new PBFormatCache($managerMock, $this->formats, $this->proxyPath);
        $result = $formatCache->getMediaUrl(
            1,
            'photo.jpeg',
            json_encode($this->storageOptions),
            '400x400',
            1,
            0
        );

        $this->assertEquals('http://example.com/photo.jpeg?v=1-0', $result);
    }

    public function testGetMediaUrlIfFileExistAndHasNotExtUrl()
    {
        $filesystemMock = $this->generateFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $managerMock = $this->generateStorageManagerMock();
        $managerMock
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($filesystemMock);
        $managerMock
            ->expects($this->once())
            ->method('getFormatFilePath')
            ->willReturn('400x400/01/1-photo.jpeg');
        $managerMock
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn(null);
        $managerMock
            ->expects($this->once())
            ->method('getRelativePath')
            ->with('400x400/01/1-photo.jpeg')
            ->willReturn('/uploads/400x400/01/1-photo.jpeg');

        $formatCache = new PBFormatCache($managerMock, $this->formats, $this->proxyPath);
        $result = $formatCache->getMediaUrl(
            1,
            'photo.jpeg',
            json_encode($this->storageOptions),
            '400x400',
            1,
            0
        );

        $this->assertEquals('/uploads/400x400/01/1-photo.jpeg?v=1-0', $result);
    }

    public function testAnalyzedMediaUrl()
    {
        $url = '/uploads/media/400x400/01/1-photo.jpeg';

        $formatCache = $this->generateFormatCacheWithWriteSuccess();

        $expected = [1, '400x400'];
        $result = $formatCache->analyzedMediaUrl($url);

        $this->assertEquals($expected, $result);
    }

    public function testAnalyzedMediaUrlWithoutIdInUrl()
    {
        $url = '/uploads/media/400x400/01/photo.jpeg';

        $formatCache = $this->generateFormatCacheWithWriteSuccess();

        $this->expectException(ImageProxyInvalidUrl::class);
        $formatCache->analyzedMediaUrl($url);
    }

    public function testAnalyzedMediaUrlWithoutCorrectIdInUrl()
    {
        $url = '/uploads/media/400x400/01/foobar-photo.jpeg';

        $formatCache = $this->generateFormatCacheWithWriteSuccess();

        $this->expectException(ImageProxyInvalidUrl::class);
        $formatCache->analyzedMediaUrl($url);
    }

    public function testAnalyzedMediaUrlWithWrongUrlStructure()
    {
        $url = 'uploads/1-photo.jpeg';

        $formatCache = $this->generateFormatCacheWithWriteSuccess();

        $this->expectException(ImageProxyInvalidUrl::class);
        $formatCache->analyzedMediaUrl($url);
    }

    public function testAnalyzedMediaUrlWithoutUrl()
    {
        $url = '';

        $formatCache = $this->generateFormatCacheWithWriteSuccess();

        $this->expectException(ImageProxyUrlNotFoundException::class);
        $formatCache->analyzedMediaUrl($url);
    }

    public function testClear()
    {
        $filesystemMock = $this->generateFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('deleteDir')
            ->willReturn(true);

        $managerMock = $this->generateStorageManagerMock();
        $managerMock
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($filesystemMock);

        $formatCache = new PBFormatCache($managerMock, $this->formats, $this->proxyPath);
        $formatCache->clear();
    }

    protected function generateFormatCacheWithWriteSuccess()
    {
        $filesystemMock = $this->generateFilesystemMock();
        $filesystemMock->method('write')->willReturn(true);

        $managerMock = $this->generateStorageManagerMock();
        $managerMock->method('getFilesystem')->willReturn($filesystemMock);

        return new PBFormatCache($managerMock, $this->formats, $this->proxyPath);
    }

    protected function generateFormatCacheWithWriteException()
    {
        $filesystemMock = $this->generateFilesystemMock();
        $filesystemMock->method('write')->willThrowException(new IOException('IOException'));

        $managerMock = $this->generateStorageManagerMock();
        $managerMock->method('getFilesystem')->willReturn($filesystemMock);

        return new PBFormatCache($managerMock, $this->formats, $this->proxyPath);
    }

    protected function generateFormatCacheWithHasSuccess()
    {
        $filesystemMock = $this->generateFilesystemMock();
        $filesystemMock->method('has')->willReturn(true);
        $filesystemMock->method('delete')->willReturn(true);

        $managerMock = $this->generateStorageManagerMock();
        $managerMock->method('getFilesystem')->willReturn($filesystemMock);

        return new PBFormatCache($managerMock, $this->formats, $this->proxyPath);
    }


//    /**
//     * @return \PHPUnit_Framework_MockObject_MockObject|PBStorageManager
//     */
//    protected function generateStorageManagerMock()
//    {
//        $mock = $this->getMockBuilder(PBStorageManager::class)
//            ->disableOriginalConstructor()
//            ->setMethods(['getFilesystem'])
//            ->getMock();
//
//        $mock
//            ->method('getFilesystem')
//            ->willReturn($this->generateFilesystemMock());
//
//        return $mock;
//    }
//
//    /**
//     * @return \PHPUnit_Framework_MockObject_MockObject|Filesystem
//     */
//    protected function generateFilesystemMock()
//    {
//        $mock = $this->getMockBuilder(Filesystem::class)
//            ->setConstructorArgs([$this->generateAdapterMock()])
//            ->setMethods(['write'])
//            ->getMock();
//
//        $mock
//            ->method('write')
//            ->willReturn(true);
//
//        return $mock;
//    }
//
//    /**
//     * @return \PHPUnit_Framework_MockObject_MockObject|NullAdapter
//     */
//    protected function generateAdapterMock()
//    {
//        $mock = $this->getMockBuilder(NullAdapter::class)
//            ->getMock();
//
//        return $mock;
//    }
}