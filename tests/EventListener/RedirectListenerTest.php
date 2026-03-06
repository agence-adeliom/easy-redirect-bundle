<?php

namespace Adeliom\EasyRedirectBundle\Tests\EventListener;

use Adeliom\EasyRedirectBundle\EventListener\RedirectListener;
use Adeliom\EasyRedirectBundle\Service\RedirectManager;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestRedirect;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(\Adeliom\EasyRedirectBundle\EventListener\RedirectListener::class)]
final class RedirectListenerTest extends TestCase
{
    public function testListenerSetsRedirectResponseForMainRequest(): void
    {
        $redirect = new TestRedirect('/legacy', '/target', 'example.com', 302);

        $manager = $this->createMock(RedirectManager::class);
        $manager
            ->expects(self::once())
            ->method('findAndUpdate')
            ->with('/legacy', 'example.com')
            ->willReturn($redirect);

        $listener = new RedirectListener($manager);
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('https://example.com/legacy'),
            HttpKernelInterface::MAIN_REQUEST
        );

        $listener->onKernelRequest($event);

        self::assertInstanceOf(RedirectResponse::class, $event->getResponse());
        self::assertSame('/target', $event->getResponse()->getTargetUrl());
        self::assertSame(302, $event->getResponse()->getStatusCode());
    }

    public function testListenerIgnoresSubRequests(): void
    {
        $manager = $this->createMock(RedirectManager::class);
        $manager->expects(self::never())->method('findAndUpdate');

        $listener = new RedirectListener($manager);
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('https://example.com/legacy'),
            HttpKernelInterface::SUB_REQUEST
        );

        $listener->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testListenerLeavesRequestUntouchedWhenNoRedirectMatches(): void
    {
        $manager = $this->createMock(RedirectManager::class);
        $manager
            ->expects(self::once())
            ->method('findAndUpdate')
            ->with('/missing', 'example.com')
            ->willReturn(null);

        $listener = new RedirectListener($manager);
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('https://example.com/missing'),
            HttpKernelInterface::MAIN_REQUEST
        );

        $listener->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }
}
