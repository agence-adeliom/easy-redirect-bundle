<?php

declare(strict_types=1);

namespace Adeliom\EasyRedirectBundle\Tests\Admin;

use Adeliom\EasyRedirectBundle\Admin\EasyRedirectTrait;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class EasyRedirectTraitTest extends TestCase
{
    public function testConfigRedirectEntryReturnsMenuItems(): void
    {
        $bag = new ParameterBag([
            'easy_redirect.redirect_class' => 'Redirect',
            'easy_redirect.not_found_class' => 'NotFound',
        ]);
        $container = new Container($bag);
        $container->set('parameter_bag', $bag);

        $object = new class($container) {
            use EasyRedirectTrait;
            public function __construct(public Container $container) {}
        };

        $items = iterator_to_array($object->configRedirectEntry());
        self::assertCount(3, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(MenuItemInterface::class, $item);
        }
    }
}
