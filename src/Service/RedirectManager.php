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
        private string $class,
        /**
         * @readonly
         */
        private EntityManager $em
    ) {
    }

    public function findAndUpdate(string $source): ?Redirect
    {
        $redirect = $this->em->getRepository($this->class)->findOneBy(['source' => $source]);

        if (!$redirect instanceof \Adeliom\EasyRedirectBundle\Entity\Redirect) {
            return null;
        }

        $redirect->increaseCount();
        $redirect->updateLastAccessed();

        $this->em->flush();

        return $redirect;
    }
}
