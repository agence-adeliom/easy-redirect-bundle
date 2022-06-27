<?php

namespace Adeliom\EasyRedirectBundle\Admin;


use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use SM\Factory\Factory;
use SM\Factory\FactoryInterface;
use Sylius\Bundle\MoneyBundle\Formatter\MoneyFormatterInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Dashboard\DashboardStatisticsProviderInterface;
use Sylius\Component\Core\Dashboard\SalesDataProviderInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Core\Repository\ShipmentRepositoryInterface;
use Sylius\Component\Mailer\Sender\Sender;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class NotFoundCrudCrontroller extends AbstractCrudController
{
    private ParameterBagInterface $parameterBag;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(ParameterBagInterface $parameterBag, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->parameterBag = $parameterBag;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, "easy_redirect.not_founds")
            ->setPageTitle(Crud::PAGE_DETAIL, fn($entity) => $entity->getPath())
            ->setEntityLabelInSingular('easy_redirect.not_found')
            ->setEntityLabelInPlural('easy_redirect.not_founds')
            ->showEntityActionsInlined(true)
            ->setFormOptions([
                'validation_groups' => ['Default']
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->disable(Action::DETAIL);
        $actions->disable(Action::NEW);
        $actions->disable(Action::EDIT);
        $actions->disable(Action::DELETE);
        $actions->disable(Action::BATCH_DELETE);

        $createRedirection = Action::new('createRedirection', 'easy_redirect.create_redirection', 'fa fa-reply')
            ->linkToCrudAction('createRedirection');
        $actions->add(Crud::PAGE_INDEX, $createRedirection);
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new("path", "easy_redirect.form.path")->hideOnForm();
        yield TextField::new("fullUrl", "easy_redirect.form.fullUrl")->hideOnForm();
        yield TextField::new("referer", "easy_redirect.form.referer")->hideOnForm();
        yield DateTimeField::new("timestamp", "easy_redirect.form.timestamp")->hideOnForm();
    }


    public function createRedirection(AdminContext $context)
    {
        if($notFound = $context->getEntity()->getInstance()){
            $redirectCrud = $context->getCrudControllers()->findCrudFqcnByEntityFqcn($this->parameterBag->get('easy_redirect.redirect_class'));
            return $this->redirect(
                $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController($redirectCrud)
                    ->setAction(Action::NEW)
                    ->set("not_found", $notFound->getPath())
                    ->generateUrl()
            );
        }
        return $this->redirect(
            $context->getRequest()->headers->get('referer')
        );
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            ParameterBagInterface::class => '?'.ParameterBagInterface::class
        ]);
    }

}
