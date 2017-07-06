<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Manager;

use PB\Bundle\SuluStorageBundle\Manager\FlysystemFileManager;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class FlysystemFileManagerTest extends AbstractTests
{
    public function testConstruction()
    {
        $fileMock = $this->generateFlysystemFileMock();
        $pathResolverMock = $this->generatePathResolverMock();
        $urlResolverMock = $this->generateUrlResolverMock();

        $manager = new FlysystemFileManager($fileMock, $pathResolverMock, $urlResolverMock);

        $this->assertEquals($fileMock, $manager->getFile());
        $this->assertEquals($pathResolverMock, $manager->getPathResolver());
        $this->assertEquals($urlResolverMock, $manager->getUrlResolver());
    }
}