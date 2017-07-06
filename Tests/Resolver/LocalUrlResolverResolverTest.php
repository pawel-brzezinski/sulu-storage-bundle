<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Resolver;

use Aws\S3\S3Client;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Adapter\Local;
use PB\Bundle\SuluStorageBundle\Resolver\LocalUrlResolver;
use PB\Bundle\SuluStorageBundle\Resolver\Exception\WrongFlysystemAdapterException;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class LocalUrlResolverResolverTest extends AbstractTests
{
    public function testGetUrlWhenAdapterIsInstanceOfFlysystemLocalAdapter()
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

        $resolver = new LocalUrlResolver(__DIR__ . '/../app');

        $this->assertEquals(
            '/Resources/test.gif',
            $resolver->getUrl($adapterMock, 'test.gif')
        );
    }

    public function testGetUrlWhenAdapterIsNotInstanceOfFlysystemLocalAdapter()
    {
        $adapterMock = $this->getMockBuilder(NullAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resolver = new LocalUrlResolver(__DIR__ . '/../app');

        $this->expectException(WrongFlysystemAdapterException::class);
        $resolver->getUrl($adapterMock, 'foo/test.gif');
    }
}
