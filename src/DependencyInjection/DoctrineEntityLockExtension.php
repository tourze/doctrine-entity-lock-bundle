<?php

declare(strict_types=1);

namespace Tourze\DoctrineEntityLockBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class DoctrineEntityLockExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
