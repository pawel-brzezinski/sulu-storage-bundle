<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Flysystem\Plugin\ContentPath;

use Aws\S3\S3ClientInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use PB\Bundle\SuluStorageBundle\Flysystem\Plugin\ContentPath\AwsS3v3ContentPathPlugin;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class AwsS3v3ContentPathPluginTest extends AbstractContentPathPluginTestCase
{
    /** @var string */
    protected $pluginClass = AwsS3v3ContentPathPlugin::class;

    /** @var string */
    protected $adapterClass = AwsS3Adapter::class;

    /** @var ObjectProphecy|AwsS3Adapter */
    protected $adapterMock;

    public function handleDataProvider()
    {
        return [
            'use non-cached adapter' => [false],
            'use cached adapter' => [true],
        ];
    }

    /**
     * @dataProvider handleDataProvider
     *
     * @param bool $useCachedAdapter
     */
    public function testHandle($useCachedAdapter = false)
    {
        // Given
        $path = '/foo/bar/example.jpg';

        $expectedPathWithPrefix = '/prefix/to/foo/bar/example.jpg';
        $expectedBucket = 'test-bucket';
        $expectedContentPath = 'https://example.com/test-bucet/prefix/to/foo/bar/example.jpg';

        // Mock CachedAdapter::getAdapter()
        if (true === $useCachedAdapter) {
            $this->cachedAdapterMock->getAdapter()->shouldBeCalledTimes(1)->willReturn($this->adapterMock->reveal());
        }
        // End

        // Mock AwsS3Adapter::applyPathPrefix()
        $this->adapterMock->applyPathPrefix($path)->shouldBeCalledTimes(1)->willReturn($expectedPathWithPrefix);
        // End

        // Mock AwsS3Adapter::getBucket()
        $this->adapterMock->getBucket()->shouldBeCalledTimes(1)->willReturn($expectedBucket);
        // End

        // Mock AwsS3Adapter::getClient()
        /** @var ObjectProphecy|S3ClientInterface $clientMock */
        $clientMock = $this->prophesize(S3ClientInterface::class);
        $this->adapterMock->getClient()->shouldBeCalledTimes(1)->willReturn($clientMock->reveal());
        // End

        // Mock S3ClientInterface::getObjectUrl()
        $clientMock->getObjectUrl($expectedBucket, $expectedPathWithPrefix)->shouldBeCalledTimes(1)->willReturn($expectedContentPath);
        // End

        /** @var AwsS3v3ContentPathPlugin $pluginUnderTest */
        $pluginUnderTest = $this->buildPlugin($useCachedAdapter);

        // When
        $actual = $pluginUnderTest->handle($path);

        // Then
        $this->assertSame($expectedContentPath, $actual);
    }
}
