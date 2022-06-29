<?php

namespace Adeliom\EasyRedirectBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Adeliom\EasyRedirectBundle\Entity\Redirect;

class NotFoundManager
{
    /**
     * @param string $class The Redirect class name
     */
    public function __construct(private string $class, private EntityManager $em)
    {
    }

    public function createFromRequest(Request $request): object
    {
        $notFound = new $this->class(
            $request->getPathInfo(),
            $request->getUri(),
            $request->server->get('HTTP_REFERER')
        );

        $this->em->persist($notFound);
        $this->em->flush();

        return $notFound;
    }

    /**
     * Deletes NotFound entities for a Redirect's path.
     */
    public function removeForRedirect(Redirect $redirect): void
    {
        $notFounds = $this->em->getRepository($this->class)->findBy(['path' => $redirect->getSource()]);

        foreach ($notFounds as $notFound) {
            $this->em->remove($notFound);
        }

        $this->em->flush();
    }
}
