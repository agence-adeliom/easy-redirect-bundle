<?php

namespace Adeliom\EasyRedirectBundle\Admin;


use Doctrine\DBAL\Types\TextType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

abstract class RedirectCrudCrontroller extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, "easy_redirect.manage_redirects")
            ->setPageTitle(Crud::PAGE_NEW, "easy_redirect.new_redirect")
            ->setPageTitle(Crud::PAGE_EDIT, "easy_redirect.edit_redirect")
            ->setPageTitle(Crud::PAGE_DETAIL, function ($entity) {
                return $entity->getSource();
            })
            ->setEntityLabelInSingular('easy_redirect.redirect')
            ->setEntityLabelInPlural('easy_redirect.redirects')
            ->setFormOptions([
                'validation_groups' => ['Default']
            ]);
    }


    public function createEntity(string $entityFqcn)
    {
        $context = $this->get(AdminContextProvider::class)->getContext();
        $request = $context->getRequest();
        return new $entityFqcn($request->query->get("not_found"));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new("source", "easy_redirect.form.source")->setRequired(true)->setColumns("col-12 col-sm-6 col-md-5");
        yield TextField::new("destination", "easy_redirect.form.destination")->setRequired(true)->setColumns("col-12 col-sm-6 col-md-5");
        yield ChoiceField::new("status", "easy_redirect.form.status")->setColumns("col-12 col-md-2")
            ->setChoices([
                "301" => "301",
                "302" => "302",
                "410" => "410",
            ])
            ->renderAsBadges()
            ->renderAsNativeWidget()
            ->setRequired(true);

        yield IntegerField::new("count", "easy_redirect.form.count")->hideOnForm();
        yield DateTimeField::new("lastAccessed", "easy_redirect.form.lastAccessed")->hideOnForm();
    }
}
