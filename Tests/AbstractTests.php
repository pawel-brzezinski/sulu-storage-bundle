<?php

namespace PB\Bundle\SuluStorageBundle\Tests;

use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\File;
use League\Flysystem\Filesystem;
use PB\Bundle\SuluStorageBundle\Manager\FlysystemFileManager;
use PB\Bundle\SuluStorageBundle\Manager\PBStorageManager;
use PB\Bundle\SuluStorageBundle\Resolver\ExternalUrlResolverInterface;
use PB\Bundle\SuluStorageBundle\Resolver\PathResolverInterface;
use PB\Bundle\SuluStorageBundle\Storage\PBStorage;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\MediaBundle\Entity\FileVersion;
use Sulu\Component\PHPCR\PathCleanup;

/**
 * Abstract for PHPUnit test classes.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 * @abstract
 */
abstract class AbstractTests extends TestCase
{
    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $masterManager
     * @param null|\PHPUnit_Framework_MockObject_MockObject $replicaManager
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function generateStorageMock($masterManager, $replicaManager = null)
    {
        $mock = $this->getMockBuilder(PBStorage::class)
            ->setConstructorArgs([$masterManager, $replicaManager])
            ->setMethods(['getMediaUrl', 'getFileManager', 'isFileExist'])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function generateStorageManagerMock()
    {
        $mock = $this->getMockBuilder(PBStorageManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilesystem', 'getFormatFilePath', 'getUrl'])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function generateFilesystemMock()
    {
        $mock = $this->getMockBuilder(Filesystem::class)
            ->setConstructorArgs([$this->generateAdapterMock()])
            ->setMethods(['has', 'write', 'delete', 'deleteDir', 'readStream', 'read', 'get'])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function generateAdapterMock()
    {
        $mock = $this->getMockBuilder(NullAdapter::class)
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function generateFlysystemFileManagerMock()
    {
        $mock = $this->getMockBuilder(FlysystemFileManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFile', 'getPathResolver', 'getExternalUrlResolver'])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function generateFlysystemFileMock()
    {
        $mock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getFilesystem', 'getPath', 'read', 'getTimestamp', 'getMetadata', 'getSize', 'getMimeType', 'isFile'
            ])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function generatePathResolverMock()
    {
        $mock = $this->getMockBuilder(PathResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFullPath'])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function generateExtUrlResolverMock()
    {
        $mock = $this->getMockBuilder(ExternalUrlResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function generatePathCleanerMock()
    {
        $mock = $this->getMockBuilder(PathCleanup::class)
            ->disableOriginalConstructor()
            ->setMethods(['cleanup'])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function generateFileVersionMock()
    {
        $mock = $this->getMockBuilder(FileVersion::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getStorageOptions'])
            ->getMock();

        return $mock;
    }
}