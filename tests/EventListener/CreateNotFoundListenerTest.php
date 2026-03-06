<?php

namespace Adeliom\EasyRedirectBundle\Tests\EventListener;

use Adeliom\EasyRedirectBundle\EventListener\CreateNotFoundListener;
use Adeliom\EasyRedirectBundle\Service\NotFoundManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(\Adeliom\EasyRedirectBundle\EventListener\CreateNotFoundListener::class)]
#[CoversClass(\Adeliom\EasyRedirectBundle\EventListener\NotFoundListener::class)]
final class CreateNotFoundListenerTest extends TestCase
{
    public function testListenerCreatesNotFoundForMain404Requests(): void
    {
        $request = Request::create('https://example.com/missing');
        $manager = $this->createMock(NotFoundManager::class);
        $manager->expects(self::once())->method('createFromRequest')->with($request);

        $listener = new CreateNotFoundListener($manager);
        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new NotFoundHttpException()
        );

        $listener->onKernelException($event);
        self::assertTrue($listener->isNotFoundException($event));
    }

    public function testListenerIgnoresSubRequestsAndNon404Exceptions(): void
    {
        $manager = $this->createMock(NotFoundManager::class);
        $manager->expects(self::never())->method('createFromRequest');

        $listener = new CreateNotFoundListener($manager);

        $subRequestEvent = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('https://example.com/missing'),
            HttpKernelInterface::SUB_REQUEST,
            new NotFoundHttpException()
        );
        $listener->onKernelException($subRequestEvent);
        self::assertFalse($listener->isNotFoundException($subRequestEvent));

        $errorEvent = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('https://example.com/error'),
            HttpKernelInterface::MAIN_REQUEST,
            new \RuntimeException('boom')
        );
        $listener->onKernelException($errorEvent);
        self::assertFalse($listener->isNotFoundException($errorEvent));
    }
}
