<?php

namespace PB\Bundle\SuluStorageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
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

        $rootNode
            ->children()
                ->enumNode('provider')
                    ->info('Available providers: Flysystem')
                    ->values(['flysystem'])
                    ->isRequired()
                ->end()
                ->arrayNode('flysystem')
                    ->info('Configuration for Flysystem provider')
                    ->children()
                        ->arrayNode('filesystem')
                            ->children()
                                ->scalarNode('storage')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('format_cache')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('segments')
                    ->cannotBeEmpty()
                    ->defaultValue(10)
                ->end()
                ->scalarNode('logger')
                    ->cannotBeEmpty()
                    ->defaultValue('logger')
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    $fsConfig = isset($v['flysystem']) ? $v['flysystem'] : null;
                    $fsfConfig = isset($fsConfig['filesystem']) ? $fsConfig['filesystem'] : null;

                    return 'flysystem' === $v['provider'] && null === $fsfConfig;
                })
                ->thenInvalid('Flysystem configuration must be defined.')
            ->end()
        ;

        return $treeBuilder;
    }
}
