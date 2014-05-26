<?php

namespace Ekyna\Bundle\FileManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekyna_file_manager');

        $rootNode
            ->children()
                ->scalarNode('output_dir')->defaultValue('')->end()
                ->arrayNode('systems')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('root_path')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->arrayNode('upload_roles')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('allowed_types')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('forbidden_types')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('delete_roles')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
