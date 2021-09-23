<?php

namespace Adeliom\EasyRedirectBundle\Service;

use Doctrine\ORM\EntityManager;
use Adeliom\EasyRedirectBundle\Entity\Redirect;

class RedirectManager
{
    private $class;

    private $em;

    /**
     * @param string $class The Redirect class name
     */
    public function __construct($class, EntityManager $em)
    {
        $this->class = $class;
        $this->em = $em;
    }

    /**
     * @param string $source
     *
     * @return Redirect|null
     */
    public function findAndUpdate($source)
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
