<?php

namespace Adeliom\EasyRedirectBundle\Tests\DependencyInjection;

use Adeliom\EasyRedirectBundle\DependencyInjection\Configuration;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestNotFound;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestRedirect;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testConfigurationAppliesDefaults(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
            'redirect_class' => TestRedirect::class,
        ]]);

        self::assertSame(TestRedirect::class, $config['redirect_class']);
        self::assertNull($config['not_found_class']);
        self::assertTrue($config['remove_not_founds']);
        self::assertNull($config['model_manager_name']);
    }

    public function testConfigurationAcceptsBothModelClasses(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
            'redirect_class' => TestRedirect::class,
            'not_found_class' => TestNotFound::class,
            'remove_not_founds' => false,
            'model_manager_name' => 'custom',
        ]]);

        self::assertSame(TestRedirect::class, $config['redirect_class']);
        self::assertSame(TestNotFound::class, $config['not_found_class']);
        self::assertFalse($config['remove_not_founds']);
        self::assertSame('custom', $config['model_manager_name']);
    }

    public function testConfigurationRejectsInvalidRedirectClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new Processor())->processConfiguration(new Configuration(), [[
            'redirect_class' => \stdClass::class,
        ]]);
    }
}
