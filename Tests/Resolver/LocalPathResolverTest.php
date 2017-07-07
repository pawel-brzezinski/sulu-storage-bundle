<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Resolver;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Adapter\NullAdapter;
use PB\Bundle\SuluStorageBundle\Resolver\Exception\DirectoryNotExistException;
use PB\Bundle\SuluStorageBundle\Resolver\Exception\WrongFlysystemAdapterException;
use PB\Bundle\SuluStorageBundle\Resolver\LocalPathResolver;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class LocalPathResolverTest extends AbstractTests
{
    public function testSetAbsolutePathPrefix()
    {
        $pathPrefix = __DIR__ . '/../';

        $resolver = new LocalPathResolver();
        $resolver->setAbsolutePathPrefix($pathPrefix);

        $reflection = new \ReflectionClass($resolver);
        $property = $reflection->getProperty('absolutePathPrefix');
        $property->setAccessible(true);

        $this->assertEquals(realpath($pathPrefix), $property->getValue($resolver));
    }

    public function testSetAbsolutePathPrefixWhichNotExists()
    {
        $resolver = new LocalPathResolver();

        $this->expectException(DirectoryNotExistException::class);
        $resolver->setAbsolutePathPrefix('/foo/bar');
    }

    public function testGetRelativePath()
    {
        $adapterMock = $this->getMockBuilder(Local::class)
            ->disableOriginalConstructor()
            ->setMethods(['applyPathPrefix'])
            ->getMock();
        $adapterMock
            ->expects($this->once())
            ->method('applyPathPrefix')
            ->with('test.gif')
            ->willReturn(__DIR__ . '/../app/Resources/test.gif');

        $pathPrefix = __DIR__ . '/../';

        $resolver = new LocalPathResolver();
        $resolver->setAbsolutePathPrefix($pathPrefix);

        $this->assertEquals(
            '/app/Resources/test.gif',
            $resolver->getRelativePath($adapterMock, 'test.gif')
        );
    }

    public function testGetRelativePathWhereAdapterIsNotLocal()
    {
        $adapterMock = $this->getMockBuilder(NullAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resolver = new LocalPathResolver();

        $this->expectException(WrongFlysystemAdapterException::class);
        $resolver->getRelativePath($adapterMock, '/foo/bar.jpg');
    }
}
