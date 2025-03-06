<?php

namespace Adeliom\EasyRedirectBundle\EventListener;

use Adeliom\EasyRedirectBundle\Service\NotFoundManager;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class CreateNotFoundListener extends NotFoundListener
{
    public function __construct(
        private readonly NotFoundManager $notFoundManager
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->isNotFoundException($event)) {
            return;
        }

        try {
            $this->notFoundManager->createFromRequest($event->getRequest());
        } catch (ORMException) {
            return;
        }
    }
}
