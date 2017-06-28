<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    /**
     * @inheritdoc
     * @return array
     */
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Sulu\Bundle\MediaBundle\SuluMediaBundle(),
            new Oneup\FlysystemBundle\OneupFlysystemBundle(),
            new PB\Bundle\SuluStorageBundle\PBSuluStorageBundle(),
        ];

        return $bundles;
    }

    /**
     * @inheritdoc
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}