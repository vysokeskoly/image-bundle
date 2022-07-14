<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('vysoke_skoly_image');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('image_formats')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('width')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('height')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->arrayNode('crop')
                                ->children()
                                    ->scalarNode('x')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('y')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('x2')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('y2')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('width')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('height')
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
