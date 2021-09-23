<?php

namespace Adeliom\EasyRedirectBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Adeliom\EasyRedirectBundle\Service\NotFoundManager;

class CreateNotFoundListener extends NotFoundListener
{
    private $notFoundManager;

    public function __construct(NotFoundManager $notFoundManager)
    {
        $this->notFoundManager = $notFoundManager;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (!$this->isNotFoundException($event)) {
            return;
        }

        $this->notFoundManager->createFromRequest($event->getRequest());
    }
}
