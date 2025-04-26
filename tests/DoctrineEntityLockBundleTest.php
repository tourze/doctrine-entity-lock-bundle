<?php

namespace Tourze\DoctrineEntityLockBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineEntityLockBundle\DoctrineEntityLockBundle;

class DoctrineEntityLockBundleTest extends TestCase
{
    /**
     * 测试Bundle基本实例化
     */
    public function testBundleInstantiation(): void
    {
        $bundle = new DoctrineEntityLockBundle();
        $this->assertInstanceOf(DoctrineEntityLockBundle::class, $bundle, 'Bundle应该能够正确实例化');
    }
}
