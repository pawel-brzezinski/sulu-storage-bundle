<?php

namespace PB\Bundle\SuluStorageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pb_sulu_storage');

        $this->setFilesystemConfiguration($rootNode);

        return $treeBuilder;
    }

    /**
     * Set filesystem configuration
     *
     * @param ArrayNodeDefinition $rootNode
     *
     * @return $this
     */
    public function setFilesystemConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()

                ->arrayNode('filesystems')
                    ->info('The names of Flysystem Sulu Storage filesystems.')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    return !isset($v['filesystems']) || !is_array($v['filesystems']) || empty($v['filesystems']);
                })
                ->thenInvalid('Minimum one filesystem should be defined for PBSuluStorageBundle')
            ->end()
        ;

        return $this;
    }
}
