<?php

namespace Adeliom\EasyRedirectBundle\EventListener;

use Adeliom\EasyRedirectBundle\Service\NotFoundManager;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class CreateNotFoundListener extends NotFoundListener
{
    public function __construct(
        /**
         * @readonly
         */
        private NotFoundManager $notFoundManager
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->isNotFoundException($event)) {
            return;
        }

        $this->notFoundManager->createFromRequest($event->getRequest());
    }
}
