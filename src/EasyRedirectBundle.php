<?php

namespace Adeliom\EasyRedirectBundle;

use Adeliom\EasyRedirectBundle\DependencyInjection\EasyRedirectExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyRedirectBundle extends Bundle
{
    /**
     * @return ExtensionInterface|null The container extension
     */
    public function getContainerExtension()
    {
        return new EasyRedirectExtension();
    }
}
