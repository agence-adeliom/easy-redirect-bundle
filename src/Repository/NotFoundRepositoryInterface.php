<?php

declare(strict_types=1);

namespace Adeliom\EasyRedirectBundle\Repository;

use Adeliom\EasyRedirectBundle\Entity\NotFound;

interface NotFoundRepositoryInterface
{
    /**
     * @return NotFound[]
     */
    public function findBy(array $criteria, ?array $orderBy = null): array;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?NotFound;
}
