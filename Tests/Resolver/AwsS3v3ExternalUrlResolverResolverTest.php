<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Resolver;

use Aws\S3\S3Client;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use PB\Bundle\SuluStorageBundle\Resolver\AwsS3v3ExternalUrlResolver;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class AwsS3v3ExternalUrlResolverResolverTest extends AbstractTests
{
    public function testGetUrlWhenAdapterIsInstanceOfFlysystemAwsS3Adapter()
    {
        $awsClientMock = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['getObjectUrl'])
            ->getMock();
        $awsClientMock
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('testbucket', 'prefix/foo/test.gif')
            ->willReturn('https://testbucket.s3.eu-central-1.amazonaws.com/prefix/foo/test.gif');

        $adapterMock = $this->getMockBuilder(AwsS3Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBucket', 'applyPathPrefix', 'getClient'])
            ->getMock();
        $adapterMock
            ->expects($this->once())
            ->method('getBucket')
            ->willReturn('testbucket');
        $adapterMock
            ->expects($this->once())
            ->method('applyPathPrefix')
            ->with('foo/test.gif')
            ->willReturn('prefix/foo/test.gif');
        $adapterMock
            ->expects($this->once())
            ->method('getClient')
            ->willReturn($awsClientMock);

        $resolver = new AwsS3v3ExternalUrlResolver();

        $this->assertEquals(
            'https://testbucket.s3.eu-central-1.amazonaws.com/prefix/foo/test.gif',
            $resolver->getUrl($adapterMock, 'foo/test.gif')
        );
    }

    public function testGetUrlWhenAdapterIsNotInstanceOfFlysystemAwsS3Adapter()
    {
        $adapterMock = $this->getMockBuilder(NullAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resolver = new AwsS3v3ExternalUrlResolver();

        $this->assertNull($resolver->getUrl($adapterMock, 'foo/test.gif'));
    }
}
