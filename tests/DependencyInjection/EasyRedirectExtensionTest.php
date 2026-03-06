<?php

namespace Adeliom\EasyRedirectBundle\Tests\DependencyInjection;

use Adeliom\EasyRedirectBundle\DependencyInjection\EasyRedirectExtension;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestNotFound;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestRedirect;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EasyRedirectExtensionTest extends TestCase
{
    public function testExtensionLoadsRedirectAndNotFoundServices(): void
    {
        $container = new ContainerBuilder();
        $extension = new EasyRedirectExtension();

        $extension->load([[
            'redirect_class' => TestRedirect::class,
            'not_found_class' => TestNotFound::class,
            'model_manager_name' => 'reporting',
        ]], $container);

        self::assertSame('easy_redirect', $extension->getAlias());
        self::assertSame(TestRedirect::class, $container->getParameter('easy_redirect.redirect_class'));
        self::assertSame(TestNotFound::class, $container->getParameter('easy_redirect.not_found_class'));
        self::assertSame('doctrine.orm.reporting_entity_manager', (string) $container->getAlias('easy_redirect.entity_manager'));
        self::assertTrue($container->hasDefinition('easy_redirect.redirect_manager'));
        self::assertTrue($container->hasDefinition('easy_redirect.redirect_listener'));
        self::assertTrue($container->hasDefinition('easy_redirect.not_found_manager'));
        self::assertTrue($container->hasDefinition('easy_redirect.not_found_listener'));
        self::assertTrue($container->hasDefinition('easy_redirect.remove_not_found_subscriber'));
    }

    public function testExtensionLoadsOnlyRedirectServicesWhenNotFoundClassIsMissing(): void
    {
        $container = new ContainerBuilder();

        (new EasyRedirectExtension())->load([[
            'redirect_class' => TestRedirect::class,
            'remove_not_founds' => true,
        ]], $container);

        self::assertTrue($container->hasDefinition('easy_redirect.redirect_manager'));
        self::assertFalse($container->hasDefinition('easy_redirect.not_found_manager'));
        self::assertFalse($container->hasDefinition('easy_redirect.remove_not_found_subscriber'));
    }

    public function testExtensionRejectsEmptyConfiguration(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new EasyRedirectExtension())->load([[]], new ContainerBuilder());
    }
}
