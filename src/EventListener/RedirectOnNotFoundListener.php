<?php

namespace Adeliom\EasyRedirectBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Adeliom\EasyRedirectBundle\Service\RedirectManager;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RedirectOnNotFoundListener extends NotFoundListener
{
    public function __construct(private RedirectManager $redirectManager)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->isNotFoundException($event)) {
            return;
        }

        $redirect = $this->redirectManager->findAndUpdate($event->getRequest()->getPathInfo());

        if (null === $redirect) {
            return;
        }

        $event->setResponse(new RedirectResponse(
            $redirect->getDestination(),
            $redirect->getStatus() ?: 301
        ));
    }
}
