<?php

declare(strict_types=1);

namespace Tourze\DoctrineEntityLockBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DoctrineEntityLockBundle\DoctrineEntityLockBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(DoctrineEntityLockBundle::class)]
#[RunTestsInSeparateProcesses]
final class DoctrineEntityLockBundleTest extends AbstractBundleTestCase
{
}
