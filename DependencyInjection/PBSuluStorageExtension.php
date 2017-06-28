<?php

namespace PB\Bundle\SuluStorageBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class PBSuluStorageExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $this->manageFilesystemsConfig($container, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * Manage filesystems config
     *
     * @param ContainerBuilder $container
     * @param array $config
     * @return $this
     */
    public function manageFilesystemsConfig(ContainerBuilder $container, array $config)
    {
        $container->setParameter('pb_sulu_storage.master', $config['master']);
        $container->setParameter('pb_sulu_storage.format_cache', $config['format_cache']);

        if (isset($config['replica'])) {
            $container->setParameter('pb_sulu_storage.replica', $config['replica']);
        }

        return $this;
    }
}
