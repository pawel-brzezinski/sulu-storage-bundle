<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Flysystem\Plugin\ContentPath;

use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\AdapterInterface;
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

    protected function setUp()
    {
        $this->fsMock = $this->prophesize(Filesystem::class);
        $this->adapterMock = $this->prophesize($this->adapterClass);
    }

    protected function tearDown()
    {
        $this->fsMock = null;
        $this->adapterMock = null;
    }

    protected function buildPlugin()
    {
        // Mock Filesystem::getAdapter()
        $this->fsMock->getAdapter()->shouldBeCalledTimes(1)->willReturn($this->adapterMock->reveal());
        // End

        /** @var AbstractContentPathPlugin $plugin */
        $plugin = new $this->pluginClass();
        $plugin->setFilesystem($this->fsMock->reveal());

        return $plugin;
    }
}
