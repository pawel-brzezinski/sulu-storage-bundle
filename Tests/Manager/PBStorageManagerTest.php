<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Manager;

use PB\Bundle\SuluStorageBundle\Manager\PBStorageManager;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class PBStorageManagerTest extends AbstractTests
{
    public function testConstructionWithExtUrlResolver()
    {
        $fsMock = $this->generateFilesystemMock();
        $pathResolverMock = $this->generatePathResolverMock();
        $extUrlResolverMock = $this->generateExtUrlResolverMock();

        $manager = new PBStorageManager($fsMock, $pathResolverMock, $extUrlResolverMock);

        $this->assertEquals($fsMock, $manager->getFilesystem());
        $this->assertEquals($pathResolverMock, $manager->getPathResolver());
        $this->assertEquals($extUrlResolverMock, $manager->getUrlResolver());
    }

    public function testConstructionWithoutExtUrlResolver()
    {
        $fsMock = $this->generateFilesystemMock();
        $pathResolverMock = $this->generatePathResolverMock();

        $manager = new PBStorageManager($fsMock, $pathResolverMock);

        $this->assertNull($manager->getUrlResolver());
    }

    public function testGetUrlWithExtUrlResolver()
    {
        $fsMock = $this->generateFilesystemMock();
        $pathResolverMock = $this->generatePathResolverMock();

        $extUrlResolverMock = $this->generateExtUrlResolverMock();
        $extUrlResolverMock
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn('http://example.com/test.gif');

        $manager = new PBStorageManager($fsMock, $pathResolverMock, $extUrlResolverMock);

        $this->assertEquals('http://example.com/test.gif', $manager->getUrl('test.gif'));
    }

    public function testGetUrlWithoutExtUrlResolver()
    {
        $fsMock = $this->generateFilesystemMock();
        $pathResolverMock = $this->generatePathResolverMock();

        $manager = new PBStorageManager($fsMock, $pathResolverMock);

        $this->assertNull($manager->getUrl('test.gif'));
    }

    public function testGetFilePath()
    {
        $fsMock = $this->generateFilesystemMock();
        $pathResolverMock = $this->generatePathResolverMock();

        $manager = new PBStorageManager($fsMock, $pathResolverMock);

        $this->assertEquals('foo/test.gif', $manager->getFilePath('test.gif', 'foo'));
        $this->assertEquals('test.gif', $manager->getFilePath('test.gif'));
    }

    public function testGetFormatFilePath()
    {
        $fsMock = $this->generateFilesystemMock();
        $pathResolverMock = $this->generatePathResolverMock();

        $manager = new PBStorageManager($fsMock, $pathResolverMock);

        $expected = '400x400/foo/1-test.gif';
        $this->assertEquals($expected, $manager->getFormatFilePath(1, 'test.gif', '400x400', 'foo'));

        $expected = '400x400/1-test.gif';
        $this->assertEquals($expected, $manager->getFormatFilePath(1, 'test.gif', '400x400'));
    }

    public function testGenerateSegmentWhenSegmentsAttributeIsNotNull()
    {
        $fsMock = $this->generateFilesystemMock();
        $pathResolverMock = $this->generatePathResolverMock();

        $manager = new PBStorageManager($fsMock, $pathResolverMock, null, 10);
        $this->assertNotNull($manager->generateSegment());
    }

    public function testGenerateSegmentWhenSegmentsAttributeIsNull()
    {
        $fsMock = $this->generateFilesystemMock();
        $pathResolverMock = $this->generatePathResolverMock();

        $manager = new PBStorageManager($fsMock, $pathResolverMock);
        $this->assertNull($manager->generateSegment());
    }

    public function testGenerateUniqueFileNameWhenFileNotExist()
    {
        $fsMock = $this->generateFilesystemMock();
        $fsMock
            ->expects($this->at(0))
            ->method('has')
            ->willReturn(false);
        $pathResolverMock = $this->generatePathResolverMock();

        $manager = new PBStorageManager($fsMock, $pathResolverMock);

        $this->assertEquals('test.gif', $manager->generateUniqueFileName('test.gif', 'foo'));
    }

    public function testGenerateUniqueFileNameWhenFileExist()
    {
        $fsMock = $this->generateFilesystemMock();
        $fsMock
            ->expects($this->at(0))
            ->method('has')
            ->willReturn(true);
        $fsMock
            ->expects($this->at(1))
            ->method('has')
            ->willReturn(false);
        $pathResolverMock = $this->generatePathResolverMock();

        $manager = new PBStorageManager($fsMock, $pathResolverMock);

        $this->assertEquals('test-1.gif', $manager->generateUniqueFileName('test.gif', 'foo'));
    }
}