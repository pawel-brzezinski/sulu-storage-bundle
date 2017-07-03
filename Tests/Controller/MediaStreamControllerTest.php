<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Controller;

use PB\Bundle\SuluStorageBundle\Controller\MediaStreamController;
use PB\Bundle\SuluStorageBundle\HttpFoundation\BinaryFlysystemFileManagerResponse;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MediaStreamControllerTest extends AbstractTests
{
    public function testGetFileResponseWithoutExternalUrl()
    {
        $fileMock = $this->generateFlysystemFileMock();
        $fileMock
            ->expects($this->any())
            ->method('getTimestamp')
            ->willReturn(time());

        $fileManager = $this->generateFlysystemFileManagerMock();
        $fileManager
            ->expects($this->any())
            ->method('getFile')
            ->willReturn($fileMock);

        $storageManager = $this->generateStorageManagerMock();

        $storage = $this->generateStorageMock($storageManager);
        $storage
            ->expects($this->once())
            ->method('isFileExist')
            ->willReturn(true);
        $storage
            ->expects($this->once())
            ->method('getFileManager')
            ->willReturn($fileManager);

        $response = $this->callGetFileResponseMethod($storage);

        $this->assertInstanceOf(BinaryFlysystemFileManagerResponse::class, $response);
    }

    public function testGetFileResponseWithExternalUrl()
    {
        $storageManager = $this->generateStorageManagerMock();
        $storage = $this->generateStorageMock($storageManager);
        $storage
            ->expects($this->once())
            ->method('isFileExist')
            ->willReturn(true);
        $storage
            ->expects($this->once())
            ->method('getMediaUrl')
            ->willReturn('http://example.com/test.gif');

        $response = $this->callGetFileResponseMethod($storage);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('http://example.com/test.gif', $response->getTargetUrl());
    }

    public function testGetFileResponseIfFileNameNotExistInStorageOptions()
    {
        $storageManager = $this->generateStorageManagerMock();
        $storage = $this->generateStorageMock($storageManager);

        $response = $this->callGetFileResponseMethod($storage, true);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('404', $response->getStatusCode());
    }

    public function testGetFileResponseIfFileNotExist()
    {
        $storageManager = $this->generateStorageManagerMock();
        $storage = $this->generateStorageMock($storageManager);
        $storage
            ->expects($this->once())
            ->method('isFileExist')
            ->willReturn(false);

        $response = $this->callGetFileResponseMethod($storage);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('404', $response->getStatusCode());
    }

    public function testGetFileResponseIfFileManagerNotExist()
    {
        $storageManager = $this->generateStorageManagerMock();
        $storage = $this->generateStorageMock($storageManager);
        $storage
            ->expects($this->once())
            ->method('isFileExist')
            ->willReturn(true);

        $response = $this->callGetFileResponseMethod($storage);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('404', $response->getStatusCode());
    }

    protected function callGetFileResponseMethod($storage, $withoutStorageOptionsData = false)
    {
        $ctrlMock = $this->getMockBuilder(MediaStreamController::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $ctrlMock
            ->expects($this->at(0))
            ->method('get')
            ->with('sulu.content.path_cleaner')
            ->willReturn($this->generatePathCleanerMock());

        if (!$withoutStorageOptionsData) {
            $ctrlMock
                ->expects($this->at(1))
                ->method('get')
                ->with('sulu_media.storage')
                ->willReturn($storage);
        }

        $reflection = new \ReflectionClass(MediaStreamController::class);
        $method = $reflection->getMethod('getFileResponse');
        $method->setAccessible(true);

        $fileVersionMock = $this->generateFileVersionMock();

        if (!$withoutStorageOptionsData) {
            $fileVersionMock
                ->expects($this->any())
                ->method('getStorageOptions')
                ->willReturn(json_encode([
                    'fileName' => 'test.gif',
                ]));
        }

        return $method->invokeArgs($ctrlMock, [
            $fileVersionMock,
            'en',
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        ]);
    }
}
