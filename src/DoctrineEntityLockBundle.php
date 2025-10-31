<?php

declare(strict_types=1);

namespace Tourze\DoctrineEntityLockBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\LockServiceBundle\LockServiceBundle;

class DoctrineEntityLockBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            LockServiceBundle::class => ['all' => true],
        ];
    }
}
