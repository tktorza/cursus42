<?php

namespace Clab\RestoflashBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('clab_restoflash');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()

            ->arrayNode('parameters')
                ->isRequired()
                ->children()
                ->scalarNode('login')->isRequired()->end()
                ->scalarNode('password')->isRequired()->end()
                ->scalarNode('imei')->isRequired()->end()
                ->scalarNode('end_point')->info('ex: https://gestion.restoflash.fr/')->isRequired()->end()
                ->scalarNode('end_point_demo')->info('ex: https://demo.restoflash.fr/')->isRequired()->end()
                ->scalarNode('end_point_user')->info('ex: https://mon.restoflash.fr/')->isRequired()->end()
                ->scalarNode('end_point_user_demo')->info('ex: https://mondemo.restoflash.fr/')->isRequired()->end()
                ->end()
            ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
