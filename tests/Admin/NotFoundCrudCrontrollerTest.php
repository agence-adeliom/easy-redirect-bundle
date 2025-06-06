<?php

declare(strict_types=1);

namespace Adeliom\EasyRedirectBundle\Tests\Admin;

use Adeliom\EasyRedirectBundle\Admin\NotFoundCrudCrontroller;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class NotFoundCrudCrontrollerTest extends TestCase
{
    private function createController(): NotFoundCrudCrontroller
    {
        $bag = $this->createMock(ParameterBagInterface::class);
        $urlGenerator = (new \ReflectionClass(AdminUrlGenerator::class))->newInstanceWithoutConstructor();

        return new class($bag, $urlGenerator) extends NotFoundCrudCrontroller {
            public static function getEntityFqcn(): string
            {
                return 'NotFound';
            }
        };
    }

    public function testConfigureFieldsReturnsFields(): void
    {
        $controller = $this->createController();
        $fields = array_map(static fn(FieldInterface $f) => $f, iterator_to_array($controller->configureFields(Crud::PAGE_INDEX)));
        self::assertCount(5, $fields);
        self::assertInstanceOf(TextField::class, $fields[0]);
    }
}
