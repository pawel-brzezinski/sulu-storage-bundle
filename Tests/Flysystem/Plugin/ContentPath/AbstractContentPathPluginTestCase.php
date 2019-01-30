<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Flysystem\Plugin\ContentPath;

use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Filesystem;
use PB\Bundle\SuluStorageBundle\Flysystem\Plugin\AbstractContentPathPlugin;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
abstract class AbstractContentPathPluginTestCase extends TestCase
{
    /** @var string  */
    protected $pluginClass = AbstractContentPathPlugin::class;

    /** @var string  */
    protected $adapterClass = NullAdapter::class;

    /** @var ObjectProphecy|Filesystem */
    protected $fsMock;

    /** @var ObjectProphecy|AdapterInterface */
    protected $adapterMock;

    /** @var ObjectProphecy|CachedAdapter */
    protected $cachedAdapterMock;

    protected function setUp()
    {
        $this->fsMock = $this->prophesize(Filesystem::class);
        $this->adapterMock = $this->prophesize($this->adapterClass);
        $this->cachedAdapterMock = $this->prophesize(CachedAdapter::class);
    }

    protected function tearDown()
    {
        $this->fsMock = null;
        $this->adapterMock = null;
        $this->cachedAdapterMock = null;
    }

    protected function buildPlugin($useCachedAdapter = false)
    {
        $adapterMock = false === $useCachedAdapter ? $this->adapterMock : $this->cachedAdapterMock;

        // Mock Filesystem::getAdapter()
        $this->fsMock->getAdapter()->shouldBeCalledTimes(1)->willReturn($adapterMock->reveal());
        // End

        /** @var AbstractContentPathPlugin $plugin */
        $plugin = new $this->pluginClass();
        $plugin->setFilesystem($this->fsMock->reveal());

        return $plugin;
    }
}
