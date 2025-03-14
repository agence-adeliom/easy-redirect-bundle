<?php

namespace Adeliom\EasyRedirectBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class NotFoundListener
{
    public function isNotFoundException(ExceptionEvent $event): bool
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return false;
        }

        $exception = $event->getThrowable();
        if (!($exception instanceof HttpException)) {
            return false;
        }

        return 404 === (int) $exception->getStatusCode();
    }

    abstract public function onKernelException(ExceptionEvent $event);
}
