<?php

namespace Adeliom\EasyRedirectBundle\EventListener;

use Adeliom\EasyRedirectBundle\Service\RedirectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RedirectListener
{
    public function __construct(
        /**
         * @readonly
         */
        private readonly RedirectManager $redirectManager
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $redirect = $this->redirectManager->findAndUpdate($event->getRequest()->getPathInfo(), $event->getRequest()->getHost());

        if (!$redirect instanceof \Adeliom\EasyRedirectBundle\Entity\Redirect) {
            return;
        }

        $event->setResponse(new RedirectResponse(
            $redirect->getDestination(),
            $redirect->getStatus() ?: 301
        ));
    }
}
