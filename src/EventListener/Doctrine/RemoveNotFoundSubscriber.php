<?php

namespace Adeliom\EasyRedirectBundle\EventListener\Doctrine;

use Adeliom\EasyRedirectBundle\Entity\Redirect;
use Adeliom\EasyRedirectBundle\Service\NotFoundManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[AsDoctrineListener(Events::postPersist)]
#[AsDoctrineListener(Events::postUpdate)]
class RemoveNotFoundSubscriber
{
    public function __construct(
        /**
         * @readonly
         */
        private NotFoundManager $notFoundManager
    ) {
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
