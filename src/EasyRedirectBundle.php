<?php

namespace Adeliom\EasyRedirectBundle;

use Adeliom\EasyRedirectBundle\DependencyInjection\EasyRedirectExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyRedirectBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new EasyRedirectExtension();
    }
}
