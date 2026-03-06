<?php

namespace Adeliom\EasyRedirectBundle\Tests\Entity;

use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestNotFound;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyRedirectBundle\Entity\NotFound::class)]
final class NotFoundTest extends TestCase
{
    public function testConstructorNormalizesPathAndCapturesMetadata(): void
    {
        $time = new \DateTimeImmutable('2026-03-05 08:30:00');
        $notFound = new TestNotFound(' /missing/path?foo=bar ', 'https://example.com/missing/path?foo=bar', 'https://referrer.test', $time);

        self::assertSame('/missing/path', $notFound->getPath());
        self::assertSame('example.com', $notFound->getHost());
        self::assertSame('https://example.com/missing/path?foo=bar', $notFound->getFullUrl());
        self::assertSame('https://referrer.test', $notFound->getReferer());
        self::assertSame($time, $notFound->getTimestamp());
        self::assertNull($notFound->getId());
    }
}
