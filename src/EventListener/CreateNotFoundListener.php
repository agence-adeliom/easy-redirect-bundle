<?php

namespace Adeliom\EasyRedirectBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Adeliom\EasyRedirectBundle\Service\NotFoundManager;

class CreateNotFoundListener extends NotFoundListener
{
    public function __construct(private NotFoundManager $notFoundManager)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->isNotFoundException($event)) {
            return;
        }

        $this->notFoundManager->createFromRequest($event->getRequest());
    }
}
