<?php

namespace PB\Bundle\SuluStorageBundle;

use PB\Bundle\SuluStorageBundle\DependencyInjection\Compiler\StoragePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PBSuluStorageBundle extends Bundle
{
    /**
     * {@inheritdoc}
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new StoragePass());
    }
}
