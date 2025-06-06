<?php

declare(strict_types=1);

namespace Adeliom\EasyRedirectBundle\Tests\Service;

use Adeliom\EasyRedirectBundle\Entity\NotFound;
use Adeliom\EasyRedirectBundle\Entity\Redirect;
use Adeliom\EasyRedirectBundle\Repository\NotFoundRepositoryInterface;
use Adeliom\EasyRedirectBundle\Service\NotFoundManager;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class NotFoundManagerTest extends TestCase
{
    public function testCreateFromRequestPersistsNewNotFound(): void
    {
        $repository = $this->createMock(NotFoundRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['path' => '/unknown'])
            ->willReturn(null);

        $em = $this->createMock(EntityManager::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(NotFound::class)
            ->willReturn($repository);
        $em->expects(self::once())->method('persist');
        $em->expects(self::once())->method('flush');

        $manager = new NotFoundManager(NotFound::class, $em);
        $request = Request::create('/unknown');

        $notFound = $manager->createFromRequest($request);

        self::assertSame('/unknown', $notFound->getPath());
    }

    public function testRemoveForRedirectDeletesNotFound(): void
    {
        $notFound = new NotFound('/a', '/a');
        $repository = $this->createMock(NotFoundRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findBy')
            ->with(['path' => '/old', 'host' => 'example.com'])
            ->willReturn([$notFound]);

        $em = $this->createMock(EntityManager::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(NotFound::class)
            ->willReturn($repository);
        $em->expects(self::once())->method('remove')->with($notFound);
        $em->expects(self::once())->method('flush');

        $manager = new NotFoundManager(NotFound::class, $em);
        $redirect = new Redirect('/old', '/new', 'example.com');
        $manager->removeForRedirect($redirect);
    }
}
