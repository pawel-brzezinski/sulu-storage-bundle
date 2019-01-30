<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Flysystem\Plugin\ContentPath;

use League\Flysystem\Adapter\Local as LocalAdapter;
use PB\Bundle\SuluStorageBundle\Flysystem\Plugin\ContentPath\LocalContentPathPlugin;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class LocalContentPathPluginTest extends AbstractContentPathPluginTestCase
{
    /** @var string */
    protected $pluginClass = LocalContentPathPlugin::class;

    /** @var string */
    protected $adapterClass = LocalAdapter::class;

    /** @var ObjectProphecy|LocalAdapter */
    protected $adapterMock;

    public function handleDataProvider()
    {
        $host1 = 'http://example.com';
        $host2 = 'http://example.com/';
        $prefix = '/foo/bar';
        $path = '/10/file.jpeg';

        $expected1 = '/foo/bar/10/file.jpeg';
        $expected2 = '/10/file.jpeg';
        $expected3 = 'http://example.com/foo/bar/10/file.jpeg';
        $expected4 = 'http://example.com/10/file.jpeg';

        return [
            'get public url without host where adapter has defined prefix and is cached' => [
                $expected1, $path, $prefix, null, true,
            ],
            'get public url without host where adapter has not defined prefix and is not cached' => [
                $expected2, $path, null, null, false,
            ],
            'get public url with host where adapter has defined prefix and is cached' => [
                $expected3, $path, $prefix, $host1, true,
            ],
            'get public url with host where adapter has not defined prefix and is not cached' => [
                $expected4, $path, null, $host2, false,
            ],
        ];
    }

    /**
     * @dataProvider handleDataProvider
     *
     * @param $expected
     * @param $path
     * @param $prefix
     * @param $host
     * @param $useCachedAdapter
     */
    public function testHandle($expected, $path, $prefix, $host, $useCachedAdapter = false)
    {
        // Given
        $fullPath = $prefix.$path;

        // Mock CachedAdapter::getAdapter()
        if (true === $useCachedAdapter) {
            $this->cachedAdapterMock->getAdapter()->shouldBeCalledTimes(1)->willReturn($this->adapterMock->reveal());
        }
        // End

        // Mock LocalAdapter::applyPathPrefix()
        $this->adapterMock->applyPathPrefix($path)->shouldBeCalledTimes(1)->willReturn($fullPath);
        // End

        /** @var LocalContentPathPlugin $pluginUnderTest */
        $pluginUnderTest = $this->buildPlugin($useCachedAdapter);

        // When
        $actual = null === $host ? $pluginUnderTest->handle($path) : $pluginUnderTest->handle($path, $host);

        // Then
        $this->assertSame($expected, $actual);
    }
}
