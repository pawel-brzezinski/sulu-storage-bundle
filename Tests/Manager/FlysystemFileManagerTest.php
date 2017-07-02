<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Manager;

use PB\Bundle\SuluStorageBundle\Manager\FlysystemFileManager;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class FlysystemFileManagerTest extends AbstractTests
{
    public function testConstructionWithExtUrlResolver()
    {
        $fileMock = $this->generateFlysystemFileMock();
        $pathResolverMock = $this->generatePathResolverMock();
        $extUrlResolverMock = $this->generateExtUrlResolverMock();

        $manager = new FlysystemFileManager($fileMock, $pathResolverMock, $extUrlResolverMock);

        $this->assertEquals($fileMock, $manager->getFile());
        $this->assertEquals($pathResolverMock, $manager->getPathResolver());
        $this->assertEquals($extUrlResolverMock, $manager->getExternalUrlResolver());
    }

    public function testConstructionWithoutExtUrlResolver()
    {
        $fileMock = $this->generateFlysystemFileMock();
        $pathResolverMock = $this->generatePathResolverMock();

        $manager = new FlysystemFileManager($fileMock, $pathResolverMock);

        $this->assertNull($manager->getExternalUrlResolver());
    }
}