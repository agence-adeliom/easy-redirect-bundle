<?php

namespace Adeliom\EasyRedirectBundle\Tests\Entity;

use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestNotFound;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestRedirect;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyRedirectBundle\Entity\Redirect::class)]
final class RedirectTest extends TestCase
{
    public function testConstructorNormalizesSourceDestinationAndHost(): void
    {
        $redirect = new TestRedirect(' https://example.com/legacy/path ', ' target?foo=bar ', '', 302);

        self::assertSame('/legacy/path', $redirect->getSource());
        self::assertSame('example.com', $redirect->getHost());
        self::assertSame('/target?foo=bar', $redirect->getDestination());
        self::assertSame('302', $redirect->getStatus());
    }

    public function testCreateFromNotFoundUsesPathAndHost(): void
    {
        $notFound = new TestNotFound('/missing', 'https://example.com/missing', 'https://referrer.test');
        $redirect = TestRedirect::createFromNotFound($notFound, '/target', 410);

        self::assertSame('/missing', $redirect->getSource());
        self::assertSame('example.com', $redirect->getHost());
        self::assertSame('/target', $redirect->getDestination());
        self::assertSame('410', $redirect->getStatus());
    }

    public function testCounterAndLastAccessedCanBeUpdated(): void
    {
        $redirect = new TestRedirect('/legacy', '/target');
        $time = new \DateTimeImmutable('2026-03-05 12:00:00');

        $redirect->increaseCount();
        $redirect->increaseCount(2);
        $redirect->updateLastAccessed($time);

        self::assertSame(3, $redirect->getCount());
        self::assertSame($time, $redirect->getLastAccessed());
    }

    public function testSetHostIgnoresNullAndAllowsManualDestination(): void
    {
        $redirect = new TestRedirect('/legacy', 'https://example.com/target', 'example.com');

        $redirect->setHost(null);
        $redirect->setDestination('https://external.test/path');

        self::assertSame('example.com', $redirect->getHost());
        self::assertSame('https://external.test/path', $redirect->getDestination());
    }
}
