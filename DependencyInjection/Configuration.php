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
                ->arrayNode('master')
                    ->info('The master storage filesystem.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('filesystem')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('segments')
                            ->cannotBeEmpty()
                            ->defaultValue(10)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('replica')
                    ->info('The replication storage filesystem.')
                    ->children()
                        ->scalarNode('filesystem')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    return !isset($v['master']) || !isset($v['master']['filesystem']) || !$v['master']['filesystem'];
                })
                ->thenInvalid('Master storage filesystem must be defined.')
            ->end()
        ;

        return $this;
    }
}
