<?php

declare(strict_types=1);

namespace Adeliom\EasyRedirectBundle\DependencyInjection;

use Adeliom\EasyRedirectBundle\EventListener\CreateNotFoundListener;
use Adeliom\EasyRedirectBundle\EventListener\Doctrine\RemoveNotFoundSubscriber;
use Adeliom\EasyRedirectBundle\EventListener\RedirectListener;
use Adeliom\EasyRedirectBundle\Service\NotFoundManager;
use Adeliom\EasyRedirectBundle\Service\RedirectManager;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

class EasyRedirectExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (null === $config['redirect_class'] && null === $config['not_found_class']) {
            throw new InvalidConfigurationException('A "redirect_class" or "not_found_class" must be set for "easy_redirect".');
        }

        $loader = class_exists(Yaml::class)
            ? new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'))
            : null;

        $modelManagerName = $config['model_manager_name'] ?: 'default';

        $container->setAlias('easy_redirect.entity_manager', \sprintf('doctrine.orm.%s_entity_manager', $modelManagerName));

        if (null !== $config['redirect_class']) {
            $container->setParameter('easy_redirect.redirect_class', $config['redirect_class']);
            null !== $loader ? $loader->load('redirect.yml') : $this->registerRedirectServices($container);
        }

        if (null !== $config['not_found_class']) {
            $container->setParameter('easy_redirect.not_found_class', $config['not_found_class']);
            null !== $loader ? $loader->load('not_found.yml') : $this->registerNotFoundServices($container);
        }

        if ($config['remove_not_founds'] && null !== $config['not_found_class'] && null !== $config['redirect_class']) {
            null !== $loader ? $loader->load('remove_not_found_subscriber.yml') : $this->registerRemoveNotFoundSubscriber($container);
        }
    }

    public function getAlias(): string
    {
        return 'easy_redirect';
    }

    private function registerRedirectServices(ContainerBuilder $container): void
    {
        $container->setParameter('easy_redirect.redirect_manager.class', RedirectManager::class);
        $container->setParameter('easy_redirect.redirect_listener.class', RedirectListener::class);

        $container->setDefinition('easy_redirect.redirect_manager', new Definition(RedirectManager::class, [
            '%easy_redirect.redirect_class%',
            new Reference('easy_redirect.entity_manager'),
        ]));

        $container->setDefinition('easy_redirect.redirect_listener', (new Definition(RedirectListener::class, [
            new Reference('easy_redirect.redirect_manager'),
        ]))->addTag('kernel.event_listener', [
            'event' => 'kernel.request',
            'method' => 'onKernelRequest',
            'priority' => 100,
        ]));
    }

    private function registerNotFoundServices(ContainerBuilder $container): void
    {
        $container->setParameter('easy_redirect.not_found_manager.class', NotFoundManager::class);
        $container->setParameter('easy_redirect.not_found_listener.class', CreateNotFoundListener::class);

        $container->setDefinition('easy_redirect.not_found_manager', new Definition(NotFoundManager::class, [
            '%easy_redirect.not_found_class%',
            new Reference('easy_redirect.entity_manager'),
        ]));

        $container->setDefinition('easy_redirect.not_found_listener', (new Definition(CreateNotFoundListener::class, [
            new Reference('easy_redirect.not_found_manager'),
        ]))->addTag('kernel.event_listener', [
            'event' => 'kernel.exception',
            'method' => 'onKernelException',
        ]));
    }

    private function registerRemoveNotFoundSubscriber(ContainerBuilder $container): void
    {
        $container->setParameter('easy_redirect.remove_not_found_subscriber.class', RemoveNotFoundSubscriber::class);

        $subscriber = new Definition(RemoveNotFoundSubscriber::class, [
            new Reference('easy_redirect.not_found_manager'),
        ]);
        $subscriber->addTag('doctrine.event_listener', ['event' => 'postPersist']);
        $subscriber->addTag('doctrine.event_listener', ['event' => 'postUpdate']);

        $container->setDefinition('easy_redirect.remove_not_found_subscriber', $subscriber);
    }
}
