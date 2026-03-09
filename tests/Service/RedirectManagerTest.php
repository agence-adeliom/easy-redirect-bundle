<?php

declare(strict_types=1);

namespace Adeliom\EasyRedirectBundle\Tests\Service;

use Adeliom\EasyRedirectBundle\Service\RedirectManager;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestRedirect;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyRedirectBundle\Service\RedirectManager::class)]
final class RedirectManagerTest extends TestCase
{
    public function testFindAndUpdateReturnsNullWhenRepositoryDoesNotReturnRedirect(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with(['source' => '/missing', 'host' => 'example.com'])->willReturn(new \stdClass());

        $em = $this->createMock(EntityManager::class);
        $em->method('getRepository')->with(TestRedirect::class)->willReturn($repository);
        $em->expects(self::never())->method('flush');

        $manager = new RedirectManager(TestRedirect::class, $em);

        self::assertNull($manager->findAndUpdate('/missing', 'example.com'));
    }

    public function testFindAndUpdateIncrementsCountAndFlushes(): void
    {
        $redirect = new TestRedirect('/legacy', '/target', 'example.com');
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with(['source' => '/legacy', 'host' => 'example.com'])->willReturn($redirect);

        $em = $this->createMock(EntityManager::class);
        $em->method('getRepository')->with(TestRedirect::class)->willReturn($repository);
        $em->expects(self::once())->method('flush');

        $manager = new RedirectManager(TestRedirect::class, $em);
        $result = $manager->findAndUpdate('/legacy', 'example.com');

        self::assertSame($redirect, $result);
        self::assertSame(1, $redirect->getCount());
        self::assertNotNull($redirect->getLastAccessed());
    }
}
