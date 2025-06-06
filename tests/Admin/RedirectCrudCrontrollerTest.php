<?php

declare(strict_types=1);

namespace Adeliom\EasyRedirectBundle\Tests\Admin;

use Adeliom\EasyRedirectBundle\Admin\RedirectCrudCrontroller;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use PHPUnit\Framework\TestCase;

final class RedirectCrudCrontrollerTest extends TestCase
{
    private function createController(): RedirectCrudCrontroller
    {
        $provider = (new \ReflectionClass(AdminContextProvider::class))->newInstanceWithoutConstructor();

        return new class($provider) extends RedirectCrudCrontroller {
            public static function getEntityFqcn(): string
            {
                return 'Redirect';
            }
        };
    }

    public function testConfigureFields(): void
    {
        $controller = $this->createController();
        $fields = array_map(static fn(FieldInterface $f) => $f, iterator_to_array($controller->configureFields(Crud::PAGE_INDEX)));
        self::assertInstanceOf(TextField::class, $fields[0]);
    }
}
