<?php

namespace PB\Bundle\SuluStorageBundle;

use PB\Bundle\SuluStorageBundle\DependencyInjection\Compiler\StoragePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class PBSuluStorageBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new StoragePass());
    }
}
