<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Storage;

use League\Flysystem\File;
use PB\Bundle\SuluStorageBundle\Manager\FlysystemFileManager;
use PB\Bundle\SuluStorageBundle\Manager\PBStorageManager;
use PB\Bundle\SuluStorageBundle\Storage\PBStorage;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;
use Sulu\Bundle\MediaBundle\Media\Exception\ImageProxyMediaNotFoundException;

class PBStorageTest extends AbstractTests
{
    protected $filePath = __DIR__ . '/../app/Resources/test.gif';

    public function testSaveWithReplication()
    {
        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $rFsMock = $this->generateFilesystemMock();

        $pathResolverMock = $this->generatePathResolverMock();

        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $rStorageManager = new PBStorageManager($rFsMock, $pathResolverMock, null);

        $storage = new PBStorage($mStorageManager, $rStorageManager);
        $saveResult = $storage->save($this->filePath, 'test.gif', 1);

        $this->assertEquals(json_encode(['segment' => '1', 'fileName' => 'test.gif']), $saveResult);
    }

    public function testLoad()
    {
        $mFsMock = $this->generateFilesystemMock();
        $pathResolverMock = $this->generatePathResolverMock();
        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);

        $this->expectException(\RuntimeException::class);
        $storage->load('test.gif', 1, json_encode(['segment' => '1', 'fileName' => 'test.gif']));
    }

    public function testLoadAsStringIfFileExist()
    {
        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $mFsMock
            ->expects($this->once())
            ->method('read')
            ->willReturn(file_get_contents($this->filePath));

        $pathResolverMock = $this->generatePathResolverMock();
        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);

        $result = $storage->loadAsString('test.gif', 1, json_encode(['segment' => '1', 'fileName' => 'test.gif']));

        $this->assertEquals(file_get_contents($this->filePath), $result);
    }

    public function testLoadAsStringIfFileNotExist()
    {
        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $pathResolverMock = $this->generatePathResolverMock();
        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);

        $this->expectException(ImageProxyMediaNotFoundException::class);
        $storage->loadAsString('test.gif', 1, json_encode(['segment' => '1', 'fileName' => 'test.gif']));
    }

    public function testRemoveIfFileExistWithReplication()
    {
        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $mFsMock
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $rFsMock = $this->generateFilesystemMock();
        $rFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $rFsMock
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $pathResolverMock = $this->generatePathResolverMock();

        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $rStorageManager = new PBStorageManager($rFsMock, $pathResolverMock);
        $storage = new PBStorage($mStorageManager, $rStorageManager);

        $storageOption = json_encode(['segment' => '1', 'fileName' => 'test.gif']);
        $this->assertTrue($storage->remove($storageOption));
    }

    public function testRemoveIfFileExistWithoutReplication()
    {
        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $mFsMock
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $pathResolverMock = $this->generatePathResolverMock();

        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);

        $storageOption = json_encode(['segment' => '1', 'fileName' => 'test.gif']);
        $this->assertTrue($storage->remove($storageOption));
    }

    public function testRemoveIfFileNotExist()
    {
        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $pathResolverMock = $this->generatePathResolverMock();

        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);

        $storageOption = json_encode(['segment' => '1', 'fileName' => 'test.gif']);
        $this->assertFalse($storage->remove($storageOption));
    }

    public function testRemoveIfStorageOptionHasNoFileName()
    {
        $mFsMock = $this->generateFilesystemMock();
        $pathResolverMock = $this->generatePathResolverMock();
        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);

        $storageOption = json_encode(['segment' => '1']);
        $this->assertFalse($storage->remove($storageOption));
    }

    public function testIsFileExistIfFileExist()
    {
        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->at(0))
            ->method('has')
            ->willReturn(true);
        $mFsMock
            ->expects($this->at(1))
            ->method('has')
            ->willReturn(false);

        $pathResolverMock = $this->generatePathResolverMock();

        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);

        $storageOption = json_encode(['segment' => '1', 'fileName' => 'test.gif']);

        $this->assertTrue($storage->isFileExist('test.gif', $storageOption));
        $this->assertFalse($storage->isFileExist('test.gif', $storageOption));
    }

    public function testGetMediaUrl()
    {
        $mFsMock = $this->generateFilesystemMock();
        $pathResolverMock = $this->generatePathResolverMock();
        $extUrlResolverMock = $this->generateExtUrlResolverMock();
        $extUrlResolverMock
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn('http://exmaple.com/1/test.gif');

        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, $extUrlResolverMock, 1);
        $storage = new PBStorage($mStorageManager);

        $result = $storage->getMediaUrl('test.gif', json_encode(['segment' => '1', 'fileName' => 'test.gif']));
        $this->assertEquals('http://exmaple.com/1/test.gif', $result);
    }

    public function testLoadStreamIfFileExist()
    {
        $resource = fopen($this->filePath, 'r');

        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $mFsMock
            ->expects($this->once())
            ->method('readStream')
            ->willReturn($resource);

        $pathResolverMock = $this->generatePathResolverMock();
        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);


        $result = $storage->loadStream('test.gif', json_encode(['segment' => '1', 'fileName' => 'test.gif']));
        $this->assertTrue(is_resource($result));

        fclose($resource);
    }

    public function testLoadStreamIfFileNotExist()
    {
        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $pathResolverMock = $this->generatePathResolverMock();
        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);

        $this->assertNull($storage->loadStream('test.gif', ''));
    }

    public function testGetFileIfFileExistAndIsNotDir()
    {
        $fileMock = $this->generateFlysystemFileMock();
        $fileMock
            ->expects($this->once())
            ->method('isFile')
            ->willReturn(true);

        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $mFsMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($fileMock);

        $pathResolverMock = $this->generatePathResolverMock();
        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);
        $result = $storage->getFile('test.gif', json_encode(['segment' => '1', 'fileName' => 'test.gif']));

        $this->assertInstanceOf(File::class, $result);
    }

    public function testGetFileIfFileExistAndIsDir()
    {
        $fileMock = $this->generateFlysystemFileMock();
        $fileMock
            ->expects($this->once())
            ->method('isFile')
            ->willReturn(false);

        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $mFsMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($fileMock);

        $pathResolverMock = $this->generatePathResolverMock();
        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);
        $result = $storage->getFile('test.gif', json_encode(['segment' => '1', 'fileName' => 'test.gif']));

        $this->assertNull($result);
    }

    public function testGetFileIfFileNotExist()
    {
        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $pathResolverMock = $this->generatePathResolverMock();
        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);
        $result = $storage->getFile('test.gif', json_encode(['segment' => '1', 'fileName' => 'test.gif']));

        $this->assertNull($result);
    }

    public function testGetFileManagerIfFileInstanceIsNotNull()
    {
        $fileMock = $this->generateFlysystemFileMock();
        $fileMock
            ->expects($this->once())
            ->method('isFile')
            ->willReturn(true);

        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $mFsMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($fileMock);

        $pathResolverMock = $this->generatePathResolverMock();
        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);
        $result = $storage->getFileManager('test.gif', json_encode(['segment' => '1', 'fileName' => 'test.gif']));

        $this->assertInstanceOf(FlysystemFileManager::class, $result);
    }

    public function testGetFileManagerIfFileInstanceIsNull()
    {
        $mFsMock = $this->generateFilesystemMock();
        $mFsMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $pathResolverMock = $this->generatePathResolverMock();
        $mStorageManager = new PBStorageManager($mFsMock, $pathResolverMock, null, 1);
        $storage = new PBStorage($mStorageManager);
        $result = $storage->getFileManager('test.gif', json_encode(['segment' => '1', 'fileName' => 'test.gif']));

        $this->assertNull($result);
    }
}