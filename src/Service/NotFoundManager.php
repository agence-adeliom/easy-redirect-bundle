<?php


namespace Adeliom\EasyRedirectBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Adeliom\EasyRedirectBundle\Entity\NotFound;
use Adeliom\EasyRedirectBundle\Entity\Redirect;

class NotFoundManager
{
    private $class;

    private $em;

    /**
     * @param string $class The NotFound class name
     */
    public function __construct($class, EntityManager $em)
    {
        $this->class = $class;
        $this->em = $em;
    }

    /**
     * @return NotFound
     */
    public function createFromRequest(Request $request)
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
    public function removeForRedirect(Redirect $redirect)
    {
        $notFounds = $this->em->getRepository($this->class)->findBy(['path' => $redirect->getSource()]);

        foreach ($notFounds as $notFound) {
            $this->em->remove($notFound);
        }

        $this->em->flush();
    }
}
