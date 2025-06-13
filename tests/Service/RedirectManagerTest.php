<?php

declare(strict_types=1);

namespace Adeliom\EasyRedirectBundle\Tests\Service;

use Adeliom\EasyRedirectBundle\Entity\Redirect;
use Adeliom\EasyRedirectBundle\Repository\RedirectRepositoryInterface;
use Adeliom\EasyRedirectBundle\Service\RedirectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class RedirectManagerTest extends TestCase
{
    public function testFindAndUpdateReturnsNullWhenNotFound(): void
    {
        /** @var RedirectRepositoryInterface $repository */
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['source' => '/missing', 'host' => ''])
            ->willReturn(null);

        $em = $this->createMock(EntityManager::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(Redirect::class)
            ->willReturn($repository);
        $em->expects(self::never())->method('flush');

        $manager = new RedirectManager(Redirect::class, $em);

        self::assertNull($manager->findAndUpdate('/missing'));
    }

    public function testFindAndUpdateUpdatesRedirect(): void
    {
        $redirect = new Redirect('/old', '/new');

        /** @var RedirectRepositoryInterface $repository */
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($redirect);

        $em = $this->createMock(EntityManager::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(Redirect::class)
            ->willReturn($repository);
        $em->expects(self::once())->method('flush');

        $manager = new RedirectManager(Redirect::class, $em);
        $result = $manager->findAndUpdate('/old');

        self::assertSame($redirect, $result);
        self::assertSame(1, $redirect->getCount());
        self::assertInstanceOf(\DateTimeInterface::class, $redirect->getLastAccessed());
    }
}
