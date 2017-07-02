<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Resolver;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Cached\CachedAdapter;
use PB\Bundle\SuluStorageBundle\Resolver\LocalPathResolver;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class AbstractPathResolverTest extends AbstractTests
{
    public function testGetFullPathWhenAdapterIsInstanceOfFlysystemAbstractAdapter()
    {
        $adapterMock = $this->getMockBuilder(NullAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['applyPathPrefix'])
            ->getMock();
        $adapterMock
            ->expects($this->once())
            ->method('applyPathPrefix')
            ->willReturn('/prefix/foo/test.gif');

        $pathResolver = new LocalPathResolver();

        $this->assertEquals('/prefix/foo/test.gif', $pathResolver->getFullPath($adapterMock, 'foo/test.gif'));
    }

    public function testGetFullPathWhenAdapterIsNotInstanceOfFlysystemAbstractAdapter()
    {
        $adapterMock = $this->getMockBuilder(AdapterInterface::class)->getMock();
        $pathResolver = new LocalPathResolver();

        $this->assertNull($pathResolver->getFullPath($adapterMock, 'foo/test'));
    }

    public function testGetFullPathWhenAdapterIsInstanceOfCachedAdapter()
    {
        $adapterMock = $this->getMockBuilder(NullAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['applyPathPrefix'])
            ->getMock();
        $adapterMock
            ->expects($this->once())
            ->method('applyPathPrefix')
            ->willReturn('/prefix/foo/test.gif');

        $cachedAdapterMock = $this->getMockBuilder(CachedAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdapter'])
            ->getMock();
        $cachedAdapterMock
            ->expects($this->once())
            ->method('getAdapter')
            ->willReturn($adapterMock);

        $pathResolver = new LocalPathResolver();

        $this->assertEquals('/prefix/foo/test.gif', $pathResolver->getFullPath($cachedAdapterMock, 'foo/test.gif'));
    }
}
