<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Flysystem\Plugin;

use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\CacheInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Replicate\ReplicateAdapter;
use PB\Bundle\SuluStorageBundle\Flysystem\Exception\InvalidAdapterException;
use PB\Bundle\SuluStorageBundle\Flysystem\Exception\InvalidFilesystemException;
use PB\Bundle\SuluStorageBundle\Flysystem\Plugin\AbstractContentPathPlugin;
use PB\Bundle\SuluStorageBundle\Tests\Fake\Flysystem\Plugin\ContentPath\FakeContentPathPlugin;
use PB\Bundle\SuluStorageBundle\Tests\Library\Utils\Reflection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class AbstractContentPathPluginTest extends TestCase
{
    public function setFilesystemDataProvider()
    {
        $cMock = $this->prophesize(CacheInterface::class);

        $nullAdapter = new NullAdapter();
        $nullAdapter2 = new NullAdapter();
        $cachedAdapter = new CachedAdapter($nullAdapter, $cMock->reveal());
        $replicaAdapter = new ReplicateAdapter($nullAdapter, $nullAdapter2);
        $replicaCachedAdapter = new CachedAdapter($replicaAdapter, $cMock->reveal());

        return [
            'non-cached adapter' => [$nullAdapter, $nullAdapter],
            'cached adapter' => [$nullAdapter, $cachedAdapter],
            'replica adapter' => [$nullAdapter, $replicaAdapter],
            'cached replica adapter' => [$nullAdapter, $replicaCachedAdapter],
        ];
    }

    /**
     * @dataProvider setFilesystemDataProvider
     *
     * @param AdapterInterface $expectedAdapter
     * @param AdapterInterface $adapter
     *
     * @throws InvalidAdapterException
     * @throws InvalidFilesystemException
     * @throws \ReflectionException
     */
    public function testSetFilesystem(AdapterInterface $expectedAdapter, AdapterInterface $adapter)
    {
        // Given

        /** @var ObjectProphecy|Filesystem $fsMock */
        $fsMock = $this->prophesize(Filesystem::class);

        // Mock Filesystem::getAdapter()
        $fsMock->getAdapter()->shouldBeCalledTimes(1)->willReturn($adapter);
        // End

        $pluginUnderTest = $this->buildPlugin();

        // When
        $pluginUnderTest->setFilesystem($fsMock->reveal());

        $fsProp = Reflection::getPropertyValue($pluginUnderTest, 'filesystem');
        $adapterProp = Reflection::getPropertyValue($pluginUnderTest, 'adapter');

        // Then
        $this->assertSame($fsMock->reveal(), $fsProp);
        $this->assertSame($expectedAdapter, $adapterProp);
    }

    public function testShouldCallSetFilesystemAndThrowInvalidFilesystemException()
    {
        // Expect
        /** @var ObjectProphecy|Filesystem $fsMock */
        $fsMock = $this->prophesize(FilesystemInterface::class);

        $this->expectException(InvalidFilesystemException::class);
        $this->expectExceptionMessage(sprintf('Filesystem "%s" is not an extension of "%s"', get_class($fsMock->reveal()), Filesystem::class));

        // Given
        $pluginUnderTest = $this->buildPlugin();

        // When
        $pluginUnderTest->setFilesystem($fsMock->reveal());
    }

    public function testShouldCallSetFilesystemAndThrowInvalidAdapterException()
    {
        // Expect
        /** @var ObjectProphecy|AdapterInterface $adapter */
        $adapter = $this->prophesize(AdapterInterface::class);
        /** @var ObjectProphecy|Filesystem $fsMock */
        $fsMock = $this->prophesize(Filesystem::class);

        $this->expectException(InvalidAdapterException::class);
        $this->expectExceptionMessage(
            sprintf('Flysystem adapter "%s" is not an instance of "%s"', get_class($adapter->reveal()), NullAdapter::class)
        );

        // Given
        // Mock Filesystem::getAdapter()
        $fsMock->getAdapter()->shouldBeCalledTimes(1)->willReturn($adapter);
        // End

        $pluginUnderTest = $this->buildPlugin();

        // When
        $pluginUnderTest->setFilesystem($fsMock->reveal());
    }

    public function testGetMethod()
    {
        // Given
        $expected = AbstractContentPathPlugin::METHOD_NAME;

        // When
        $actual = $this->buildPlugin()->getMethod();

        // Then
        $this->assertSame($expected, $actual);
    }

    private function buildPlugin()
    {
        return new FakeContentPathPlugin();
    }
}
