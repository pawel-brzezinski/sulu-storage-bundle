<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Resolver;

use League\Flysystem\AwsS3v3\AwsS3Adapter;
use PB\Bundle\SuluStorageBundle\Resolver\AwsS3v3PathResolver;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class AwsS3v3PathResolverTest extends AbstractTests
{
    public function testGetRelativePath()
    {
        $adapterMock = $this->getMockBuilder(AwsS3Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['applyPathPrefix'])
            ->getMock();
        $adapterMock
            ->expects($this->once())
            ->method('applyPathPrefix')
            ->with('foo/test.gif')
            ->willReturn('prefix/foo/test.gif');

        $resolver = new AwsS3v3PathResolver();

        $this->assertEquals('prefix/foo/test.gif', $resolver->getRelativePath($adapterMock, 'foo/test.gif'));
    }
}
