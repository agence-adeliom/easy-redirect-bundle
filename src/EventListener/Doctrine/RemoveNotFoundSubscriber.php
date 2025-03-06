<?php

namespace Adeliom\EasyRedirectBundle\EventListener\Doctrine;

use Adeliom\EasyRedirectBundle\Entity\Redirect;
use Adeliom\EasyRedirectBundle\Service\NotFoundManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Exception\ORMException;

#[AsDoctrineListener(Events::postPersist)]
#[AsDoctrineListener(Events::postUpdate)]
class RemoveNotFoundSubscriber
{
    public function __construct(
        private readonly NotFoundManager $notFoundManager
    ) {
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->remoteNotFoundForRedirect($args);
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->remoteNotFoundForRedirect($args);
    }

    private function remoteNotFoundForRedirect(PostPersistEventArgs|PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Redirect) {
            return;
        }

        try {
            $this->notFoundManager->removeForRedirect($entity);
        } catch (ORMException) {
            return;
        }
    }
}
