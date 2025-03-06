<?php

namespace Adeliom\EasyRedirectBundle\Service;

use Adeliom\EasyRedirectBundle\Entity\Redirect;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class NotFoundManager
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
    public function createFromRequest(Request $request): object
    {
        /** @var \Adeliom\EasyRedirectBundle\Repository\NotFoundRepositoryInterface $notFoundRepository */
        $notFoundRepository = $this->entityManager->getRepository($this->class);
        $notFound = $notFoundRepository->findOneBy(['path' => $request->getPathInfo()]);
        if (!$notFound) {
            $notFound = new $this->class($request->getPathInfo(), $request->getUri(), $request->server->get('HTTP_REFERER'));
            $this->entityManager->persist($notFound);
            $this->entityManager->flush();
        }

        return $notFound;
    }

    /**
     * Deletes NotFound entities for a Redirect's path.
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function removeForRedirect(Redirect $redirect): void
    {
        /** @var \Adeliom\EasyRedirectBundle\Repository\NotFoundRepositoryInterface $notFoundRepository */
        $notFoundRepository = $this->entityManager->getRepository($this->class);
        $notFounds = $notFoundRepository->findBy(['path' => $redirect->getSource(), 'host' => $redirect->getHost()]);

        foreach ($notFounds as $notFound) {
            $this->entityManager->remove($notFound);
        }

        $this->entityManager->flush();
    }
}
