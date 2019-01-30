<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Flysystem\Plugin;

use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
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
    public function testSetFilesystem()
    {
        // Given
        $adapter = new NullAdapter();
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
        $this->assertSame($adapter, $adapterProp);
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
