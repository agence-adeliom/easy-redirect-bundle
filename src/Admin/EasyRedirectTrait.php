<?php

namespace Adeliom\EasyRedirectBundle\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use Iterator;

trait EasyRedirectTrait
{
    /**
     * @return Iterator<MenuItemInterface>
     */
    public function configRedirectEntry(): iterable
    {
        $parameterBag = $this->container->get('parameter_bag');
        yield MenuItem::section('easy_redirect.redirects');
        yield MenuItem::linkToCrud('easy_redirect.redirects', 'fa fa-forward', $parameterBag->get('easy_redirect.redirect_class'));
        yield MenuItem::linkToCrud('easy_redirect.not_founds', 'fa fa-unlink', $parameterBag->get('easy_redirect.not_found_class'));
    }
}
