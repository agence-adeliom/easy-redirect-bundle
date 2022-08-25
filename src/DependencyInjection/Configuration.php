<?php

namespace Adeliom\EasyRedirectBundle\DependencyInjection;

use Adeliom\EasyRedirectBundle\Entity\NotFound;
use Adeliom\EasyRedirectBundle\Entity\Redirect;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_redirect');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('redirect_class')
                    ->defaultNull()
                    ->validate()
                        ->ifTrue(static fn ($value) => !\is_subclass_of($value, Redirect::class))
                        ->thenInvalid('"redirect_class" must be an instance of "Adeliom\EasyRedirectBundle\Entity\Redirect"')
                    ->end()
                ->end()
                ->scalarNode('not_found_class')
                    ->defaultNull()
                    ->validate()
                        ->ifTrue(static fn ($value) => !\is_subclass_of($value, NotFound::class))
                        ->thenInvalid('"not_found_class" must be an instance of "Adeliom\EasyRedirectBundle\Entity\NotFound"')
                    ->end()
                ->end()
                ->booleanNode('remove_not_founds')
                    ->info('When enabled, when a redirect is updated or created, the NotFound entites with a matching path are removed.')
                    ->defaultTrue()
                ->end()
                ->scalarNode('model_manager_name')->defaultNull()->end()
            ->end();

        return $treeBuilder;
    }
}
