<?php

declare(strict_types=1);

namespace Adeliom\EasyRedirectBundle\Tests\Repository;

use Adeliom\EasyRedirectBundle\Repository\NotFoundRepositoryInterface;
use Adeliom\EasyRedirectBundle\Repository\RedirectRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class RepositoryInterfaceTest extends TestCase
{
    public function testInterfacesExist(): void
    {
        self::assertTrue(interface_exists(NotFoundRepositoryInterface::class));
        self::assertTrue(interface_exists(RedirectRepositoryInterface::class));
    }
}
