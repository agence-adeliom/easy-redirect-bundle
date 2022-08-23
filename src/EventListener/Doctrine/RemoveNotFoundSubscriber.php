<?php

namespace Adeliom\EasyRedirectBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Adeliom\EasyRedirectBundle\Entity\Redirect;
use Adeliom\EasyRedirectBundle\Service\NotFoundManager;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RemoveNotFoundSubscriber implements EventSubscriber
{
    public function __construct(private NotFoundManager $notFoundManager)
    {
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            'postPersist',
            'postUpdate',
        ];
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->remoteNotFoundForRedirect($args);
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->remoteNotFoundForRedirect($args);
    }

    private function remoteNotFoundForRedirect(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Redirect) {
            return;
        }

        $this->getNotFoundManager()->removeForRedirect($entity);
    }

    private function getNotFoundManager(): NotFoundManager
    {
        return $this->notFoundManager;
    }
}
