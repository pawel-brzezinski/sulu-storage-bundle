<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Flysystem\Plugin\ContentPath;

use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;
use PB\Bundle\SuluStorageBundle\Flysystem\Plugin\ContentPath\GoogleStorageContentPathPlugin;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class GoogleStorageContentPathPluginTest extends AbstractContentPathPluginTestCase
{
    /** @var string */
    protected $pluginClass = GoogleStorageContentPathPlugin::class;

    /** @var string */
    protected $adapterClass = GoogleStorageAdapter::class;

    /** @var ObjectProphecy|GoogleStorageAdapter */
    protected $adapterMock;

    public function testHandle()
    {
        // Given
        $path = '/foo/bar/example.jpg';
        $expectedContentPath = 'https://example.com/foo/bar/example.jpg';

        // Mock GoogleStorageAdapter::getUrl()
        $this->adapterMock->getUrl($path)->shouldBeCalledTimes(1)->willReturn($expectedContentPath);
        // End

        /** @var GoogleStorageContentPathPlugin $pluginUnderTest */
        $pluginUnderTest = $this->buildPlugin();

        // When
        $actual = $pluginUnderTest->handle($path);

        // Then
        $this->assertSame($expectedContentPath, $actual);
    }
}
