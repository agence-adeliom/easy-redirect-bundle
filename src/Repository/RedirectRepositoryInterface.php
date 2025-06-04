<?php

declare(strict_types=1);

namespace Adeliom\EasyRedirectBundle\Repository;

use Adeliom\EasyRedirectBundle\Entity\Redirect;

interface RedirectRepositoryInterface
{
    public function findOneBy(array $criteria, ?array $orderBy = null): ?Redirect;
}
