<?php

namespace Clab\SocialBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('clab_social');

        $rootNode
            ->children()
                ->scalarNode('api_domain')
                ->info('Enter your api domain here ex: api.click-eat.fr ')
                ->isRequired()
                ->cannotBeEmpty()
                ->end()
                ->scalarNode('tttdomain')
                ->info('Enter the ttt domain here ex: ttruct.clickeat.fr ')
                ->isRequired()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
