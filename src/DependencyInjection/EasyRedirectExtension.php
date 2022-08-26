<?php

namespace Adeliom\EasyRedirectBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class EasyRedirectExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (null === $config['redirect_class'] && null === $config['not_found_class']) {
            throw new InvalidConfigurationException('A "redirect_class" or "not_found_class" must be set for "easy_redirect".');
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        // $loader->load('services.yml');

        $modelManagerName = $config['model_manager_name'] ?: 'default';

        $container->setAlias('easy_redirect.entity_manager', \sprintf('doctrine.orm.%s_entity_manager', $modelManagerName));

        if (null !== $config['redirect_class']) {
            $container->setParameter('easy_redirect.redirect_class', $config['redirect_class']);
            $loader->load('redirect.yml');
        }

        if (null !== $config['not_found_class']) {
            $container->setParameter('easy_redirect.not_found_class', $config['not_found_class']);
            $loader->load('not_found.yml');
        }

        if ($config['remove_not_founds'] && null !== $config['not_found_class'] && null !== $config['redirect_class']) {
            $loader->load('remove_not_found_subscriber.yml');
        }
    }

    public function getAlias(): string
    {
        return 'easy_redirect';
    }
}
