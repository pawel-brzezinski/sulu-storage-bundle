<?php

namespace PB\Bundle\SuluStorageBundle\Tests;

use PB\Bundle\SuluStorageBundle\DependencyInjection\Compiler\StoragePass;
use PB\Bundle\SuluStorageBundle\PBSuluStorageBundle;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class PBSuluStorageBundleTest extends TestCase
{
    /** @var ObjectProphecy|ContainerBuilder */
    private $cbMock;

    protected function setUp()
    {
        parent::setUp();

        $this->cbMock = $this->prophesize(ContainerBuilder::class);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->cbMock = null;
    }

    public function testBuild()
    {
        // Given
        $bundle = new PBSuluStorageBundle();

        // Mock ContainerBuilder::addCompilerPass()
        $this->cbMock->addCompilerPass(Argument::type(StoragePass::class))->shouldBeCalledTimes(1);
        // End

        // When
        $bundle->build($this->cbMock->reveal());
    }
}
