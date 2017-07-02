<?php

namespace PB\Bundle\SuluStorageBundle\Tests;

use PB\Bundle\SuluStorageBundle\DependencyInjection\Compiler\StoragePass;
use PB\Bundle\SuluStorageBundle\PBSuluStorageBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PBSuluStorageBundleTest extends AbstractTests
{
    public function testSubClassOfBundle()
    {
        $this->assertInstanceOf(Bundle::class, new PBSuluStorageBundle());
    }

    public function testCompilerPassOnBuild()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addCompilerPass'])
            ->getMock();
        $container->expects($this->exactly(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(StoragePass::class));

        $bundle = new PBSuluStorageBundle();
        $bundle->build($container);
    }
}