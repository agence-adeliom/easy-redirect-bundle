<?php

namespace Adeliom\EasyRedirectBundle\Service;

use Adeliom\EasyRedirectBundle\Entity\Redirect;
use Doctrine\ORM\EntityManager;

class RedirectManager
{
    /**
     * @param string $class The Redirect class name
     */
    public function __construct(
        private readonly string $class,
        private readonly EntityManager $entityManager
    ) {
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\Exception\NotSupported
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function findAndUpdate(string $source, ?string $host = ""): ?Redirect
    {
        /** @var \Adeliom\EasyRedirectBundle\Repository\RedirectRepositoryInterface $redirectRepository */
        $redirectRepository = $this->entityManager->getRepository($this->class);
        $redirect = $redirectRepository->findOneBy(['source' => $source, 'host' => $host]);
        if (! $redirect instanceof Redirect) {
            return null;
        }

        $redirect->increaseCount();
        $redirect->updateLastAccessed();

        $this->entityManager->flush();

        return $redirect;
    }
}
