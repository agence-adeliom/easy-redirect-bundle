<?php

namespace Adeliom\EasyRedirectBundle;

use Adeliom\EasyRedirectBundle\DependencyInjection\EasyRedirectExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyRedirectBundle extends Bundle
{
    public function getContainerExtension(): EasyRedirectExtension
    {
        return new EasyRedirectExtension();
    }
}
