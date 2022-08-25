<?php

namespace Adeliom\EasyRedirectBundle\EventListener;

use Adeliom\EasyRedirectBundle\Service\RedirectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RedirectOnNotFoundListener extends NotFoundListener
{
    public function __construct(
        /**
         * @readonly
         */
        private RedirectManager $redirectManager
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->isNotFoundException($event)) {
            return;
        }

        $redirect = $this->redirectManager->findAndUpdate($event->getRequest()->getPathInfo());

        if (!$redirect instanceof \Adeliom\EasyRedirectBundle\Entity\Redirect) {
            return;
        }

        $event->setResponse(new RedirectResponse(
            $redirect->getDestination(),
            $redirect->getStatus() ?: 301
        ));
    }
}
