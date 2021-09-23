<?php

namespace Adeliom\EasyRedirectBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Adeliom\EasyRedirectBundle\Entity\Redirect;
use Adeliom\EasyRedirectBundle\Service\NotFoundManager;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RemoveNotFoundSubscriber implements EventSubscriber
{
    private $notFoundManager;

    public function __construct(NotFoundManager $notFoundManager)
    {
        $this->notFoundManager = $notFoundManager;
    }

    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'postUpdate',
        ];
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->remoteNotFoundForRedirect($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->remoteNotFoundForRedirect($args);
    }

    private function remoteNotFoundForRedirect(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Redirect) {
            return;
        }

        $this->getNotFoundManager()->removeForRedirect($entity);
    }

    /**
     * @return NotFoundManager
     */
    private function getNotFoundManager()
    {
        return $this->notFoundManager;
    }
}
