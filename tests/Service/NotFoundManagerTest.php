<?php

namespace Adeliom\EasyRedirectBundle\Tests\Service;

use Adeliom\EasyRedirectBundle\Service\NotFoundManager;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestNotFound;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestRedirect;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(\Adeliom\EasyRedirectBundle\Service\NotFoundManager::class)]
final class NotFoundManagerTest extends TestCase
{
    public function testCreateFromRequestReturnsExistingEntityWithoutPersisting(): void
    {
        $existing = new TestNotFound('/missing', 'https://example.com/missing');
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with(['path' => '/missing'])->willReturn($existing);

        $em = $this->createMock(EntityManager::class);
        $em->method('getRepository')->with(TestNotFound::class)->willReturn($repository);
        $em->expects(self::never())->method('persist');
        $em->expects(self::never())->method('flush');

        $manager = new NotFoundManager(TestNotFound::class, $em);

        self::assertSame($existing, $manager->createFromRequest(Request::create('https://example.com/missing')));
    }

    public function testCreateFromRequestPersistsNewEntity(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with(['path' => '/missing'])->willReturn(null);

        $em = $this->createMock(EntityManager::class);
        $em->method('getRepository')->with(TestNotFound::class)->willReturn($repository);
        $em->expects(self::once())->method('persist')->with(self::isInstanceOf(TestNotFound::class));
        $em->expects(self::once())->method('flush');

        $request = Request::create('https://example.com/missing');
        $request->server->set('HTTP_REFERER', 'https://referrer.test');

        $manager = new NotFoundManager(TestNotFound::class, $em);
        $result = $manager->createFromRequest($request);

        self::assertInstanceOf(TestNotFound::class, $result);
        self::assertSame('/missing', $result->getPath());
        self::assertSame('https://referrer.test', $result->getReferer());
    }

    public function testRemoveForRedirectDeletesMatchingEntries(): void
    {
        $redirect = new TestRedirect('/legacy', '/target', 'example.com');
        $notFoundA = new TestNotFound('/legacy', 'https://example.com/legacy');
        $notFoundB = new TestNotFound('/legacy', 'https://example.com/legacy-2');

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::once())->method('findBy')->with(['path' => '/legacy', 'host' => 'example.com'])->willReturn([$notFoundA, $notFoundB]);

        $em = $this->createMock(EntityManager::class);
        $em->method('getRepository')->with(TestNotFound::class)->willReturn($repository);
        $em->expects(self::exactly(2))->method('remove')->with(self::isInstanceOf(TestNotFound::class));
        $em->expects(self::once())->method('flush');

        (new NotFoundManager(TestNotFound::class, $em))->removeForRedirect($redirect);
    }
}
