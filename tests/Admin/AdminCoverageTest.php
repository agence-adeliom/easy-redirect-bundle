<?php

namespace Adeliom\EasyRedirectBundle\Tests\Admin;

use Adeliom\EasyRedirectBundle\Admin\EasyRedirectTrait;
use Adeliom\EasyRedirectBundle\Admin\NotFoundCrudCrontroller;
use Adeliom\EasyRedirectBundle\Admin\RedirectCrudCrontroller;
use Adeliom\EasyRedirectBundle\EasyRedirectBundle;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestNotFound;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestRedirect;
use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Context\CrudContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\RequestContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Router\AdminRouteGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\DashboardControllerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Factory\MenuFactoryInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Registry\CrudControllerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Registry\DashboardControllerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(\Adeliom\EasyRedirectBundle\Admin\EasyRedirectTrait::class)]
#[CoversClass(\Adeliom\EasyRedirectBundle\Admin\NotFoundCrudCrontroller::class)]
#[CoversClass(\Adeliom\EasyRedirectBundle\Admin\RedirectCrudCrontroller::class)]
#[CoversClass(\Adeliom\EasyRedirectBundle\EasyRedirectBundle::class)]
final class AdminCoverageTest extends TestCase
{
    public function testRedirectCrudControllerBuildsCrudConfigurationAndEntity(): void
    {
        $request = new Request(['not_found' => '/missing', 'host' => 'example.com']);
        $controller = new TestRedirectCrudController(new AdminContextProvider($this->createRequestStackWithContext($request)));

        $crudDto = $controller->configureCrud(Crud::new())->getAsDto();
        self::assertSame('easy_redirect.redirect', $crudDto->getEntityLabelInSingular());
        self::assertSame('easy_redirect.redirects', $crudDto->getEntityLabelInPlural());
        self::assertSame('easy_redirect.new_redirect', (string) $crudDto->getCustomPageTitle(Crud::PAGE_NEW));

        $entity = $controller->createEntity(TestRedirect::class);
        self::assertInstanceOf(TestRedirect::class, $entity);
        self::assertSame('/missing', $entity->getSource());
        self::assertSame('example.com', $entity->getHost());

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));
        self::assertCount(6, $fields);
        self::assertSame('source', $fields[0]->getAsDto()->getProperty());
        self::assertSame('lastAccessed', $fields[5]->getAsDto()->getProperty());
    }

    public function testNotFoundCrudControllerConfiguresCrudActionsAndFallbackRedirect(): void
    {
        $adminUrlGenerator = $this->createAdminUrlGenerator('/admin?crudAction=new');
        $controller = new TestNotFoundCrudController(new ParameterBag([
            'easy_redirect.redirect_class' => TestRedirect::class,
        ]), $adminUrlGenerator);

        $crudDto = $controller->configureCrud(Crud::new())->getAsDto();
        self::assertSame('easy_redirect.not_found', $crudDto->getEntityLabelInSingular());
        self::assertFalse($crudDto->showEntityActionsAsDropdown());

        $actionsDto = $controller->configureActions(Actions::new())->getAsDto(Crud::PAGE_INDEX);
        self::assertContains(Action::DETAIL, $actionsDto->getDisabledActions());
        self::assertContains(Action::NEW, $actionsDto->getDisabledActions());
        self::assertContains(Action::EDIT, $actionsDto->getDisabledActions());
        self::assertContains(Action::DELETE, $actionsDto->getDisabledActions());
        self::assertContains(Action::BATCH_DELETE, $actionsDto->getDisabledActions());
        self::assertNotNull($actionsDto->getAction(Crud::PAGE_INDEX, 'createRedirection'));

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));
        self::assertCount(5, $fields);
        self::assertSame('path', $fields[0]->getAsDto()->getProperty());

        $request = Request::create('https://example.com/admin');
        $request->headers->set('referer', 'https://example.com/previous');
        $context = $this->createAdminContext($request, null);

        self::assertSame('https://example.com/previous', $controller->createRedirection($context)->getTargetUrl());
        self::assertArrayHasKey(\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface::class, TestNotFoundCrudController::getSubscribedServices());
    }

    public function testTraitBuildsRedirectMenuEntries(): void
    {
        $container = new Container();
        $container->set('parameter_bag', new ParameterBag([
            'easy_redirect.redirect_class' => TestRedirect::class,
            'easy_redirect.not_found_class' => TestNotFound::class,
        ]));
        $dashboard = new TestEasyRedirectDashboardController();
        $dashboard->setContainer($container);

        $items = iterator_to_array($dashboard->configRedirectEntry());

        self::assertCount(3, $items);
        self::assertSame('easy_redirect.redirects', (string) $items[0]->getAsDto()->getLabel());
        self::assertSame('easy_redirect.not_founds', (string) $items[2]->getAsDto()->getLabel());
    }

    public function testBundleCreatesItsContainerExtension(): void
    {
        self::assertInstanceOf(\Adeliom\EasyRedirectBundle\DependencyInjection\EasyRedirectExtension::class, (new EasyRedirectBundle())->getContainerExtension());
    }

    private function createRequestStackWithContext(Request $request): RequestStack
    {
        $request->attributes->set(EA::CONTEXT_REQUEST_ATTRIBUTE, $this->createAdminContext($request));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        return $requestStack;
    }

    private function createAdminContext(Request $request, ?object $entity = null): AdminContext
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']);

        return AdminContext::forTesting(
            RequestContext::forTesting($request),
            CrudContext::forTesting(
                entityDto: new EntityDto($entity ? $entity::class : TestNotFound::class, $metadata, null, $entity),
                crudControllers: new CrudControllerRegistry([], [], [TestRedirect::class => TestRedirectCrudController::class], [])
            )
        );
    }

    private function createAdminUrlGenerator(string $generatedUrl): AdminUrlGenerator
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('https://example.com/admin'));
        $provider = new AdminContextProvider($requestStack);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn($generatedUrl);

        $cacheDir = sys_get_temp_dir().'/easyadmin-dashboard-registry-'.bin2hex(random_bytes(4));
        @mkdir($cacheDir.'/easyadmin', 0777, true);
        file_put_contents($cacheDir.'/easyadmin/routes-dashboard.php', '<?php return ["admin" => "'.addslashes(TestDashboardController::class).'::index"];');

        return new AdminUrlGenerator(
            $provider,
            $urlGenerator,
            new DashboardControllerRegistry($cacheDir, [TestDashboardController::class => 'dashboard'], ['dashboard' => TestDashboardController::class]),
            new class() implements AdminRouteGeneratorInterface {
                public function generateAll(): \Symfony\Component\Routing\RouteCollection
                {
                    return new \Symfony\Component\Routing\RouteCollection();
                }

                public function findRouteName(string $dashboardFqcn, string $crudControllerFqcn, string $actionName): ?string
                {
                    return 'admin_redirect_new';
                }

                public function usesPrettyUrls(): bool
                {
                    return true;
                }
            },
            $this->createMock(CacheItemPoolInterface::class)
        );
    }
}

final class TestRedirectCrudController extends RedirectCrudCrontroller
{
    public static function getEntityFqcn(): string
    {
        return TestRedirect::class;
    }
}

final class TestNotFoundCrudController extends NotFoundCrudCrontroller
{
    public static function getEntityFqcn(): string
    {
        return TestNotFound::class;
    }
}

final class TestDashboardController implements DashboardControllerInterface
{
    public function configureDashboard(): \EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard
    {
        return \EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard::new();
    }

    public function configureAssets(): \EasyCorp\Bundle\EasyAdminBundle\Config\Assets
    {
        return \EasyCorp\Bundle\EasyAdminBundle\Config\Assets::new();
    }

    public function configureMenuItems(): iterable
    {
        return [];
    }

    public function configureUserMenu(\Symfony\Component\Security\Core\User\UserInterface $user): \EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu
    {
        return \EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu::new();
    }

    public function configureCrud(): Crud
    {
        return Crud::new();
    }

    public function configureActions(): Actions
    {
        return Actions::new();
    }

    public function configureFilters(): \EasyCorp\Bundle\EasyAdminBundle\Config\Filters
    {
        return \EasyCorp\Bundle\EasyAdminBundle\Config\Filters::new();
    }

    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return new \Symfony\Component\HttpFoundation\Response();
    }
}

final class TestEasyRedirectDashboardController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    use EasyRedirectTrait;
}
