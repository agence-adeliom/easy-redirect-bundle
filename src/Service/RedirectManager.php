<?php

namespace Adeliom\EasyRedirectBundle\Service;

use Doctrine\ORM\EntityManager;
use Adeliom\EasyRedirectBundle\Entity\Redirect;

class RedirectManager
{
    /**
     * @param string $class The Redirect class name
     */
    public function __construct(private string $class, private EntityManager $em)
    {
    }

    public function findAndUpdate(string $source): ?Redirect
    {
        /** @var Redirect|null $redirect */
        $redirect = $this->em->getRepository($this->class)->findOneBy(['source' => $source]);

        if (null === $redirect) {
            return null;
        }

        $redirect->increaseCount();
        $redirect->updateLastAccessed();
        $this->em->flush();

        return $redirect;
    }
}
