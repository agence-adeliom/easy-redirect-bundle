<?php

namespace Adeliom\EasyRedirectBundle\Tests\EventListener\Doctrine;

use Adeliom\EasyRedirectBundle\EventListener\Doctrine\RemoveNotFoundSubscriber;
use Adeliom\EasyRedirectBundle\Service\NotFoundManager;
use Adeliom\EasyRedirectBundle\Tests\Fixtures\Entity\TestRedirect;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyRedirectBundle\EventListener\Doctrine\RemoveNotFoundSubscriber::class)]
final class RemoveNotFoundSubscriberTest extends TestCase
{
    public function testSubscriberIgnoresNonRedirectEntities(): void
    {
        $manager = $this->createMock(NotFoundManager::class);
        $manager->expects(self::never())->method('removeForRedirect');

        $subscriber = new RemoveNotFoundSubscriber($manager);
        $persistEvent = new PostPersistEventArgs(new \stdClass(), $this->createMock(EntityManagerInterface::class));
        $updateEvent = new PostUpdateEventArgs(new \stdClass(), $this->createMock(EntityManagerInterface::class));

        $subscriber->postPersist($persistEvent);
        $subscriber->postUpdate($updateEvent);
    }

    public function testSubscriberRemovesNotFoundEntriesForRedirects(): void
    {
        $redirect = new TestRedirect('/legacy', '/target', 'example.com');
        $manager = $this->createMock(NotFoundManager::class);
        $manager->expects(self::exactly(2))->method('removeForRedirect')->with($redirect);

        $subscriber = new RemoveNotFoundSubscriber($manager);
        $persistEvent = new PostPersistEventArgs($redirect, $this->createMock(EntityManagerInterface::class));
        $updateEvent = new PostUpdateEventArgs($redirect, $this->createMock(EntityManagerInterface::class));

        $subscriber->postPersist($persistEvent);
        $subscriber->postUpdate($updateEvent);
    }
}
