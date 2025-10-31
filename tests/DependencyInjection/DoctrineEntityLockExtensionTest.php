<?php

declare(strict_types=1);

namespace Tourze\DoctrineEntityLockBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DoctrineEntityLockBundle\DependencyInjection\DoctrineEntityLockExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(DoctrineEntityLockExtension::class)]
final class DoctrineEntityLockExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
