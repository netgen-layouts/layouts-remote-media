<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection;

use Netgen\Layouts\Utils\BackwardsCompatibility\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('netgen_layouts_remote_media');

        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('root_folder')
                    ->info('Root folder in Cloudinary for content browser (see README)')
                    ->defaultNull()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
