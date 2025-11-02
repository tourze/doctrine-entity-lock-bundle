<?php

declare(strict_types=1);

namespace Tourze\DoctrineEntityLockBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DoctrineEntityLockBundle\Service\EntityLockService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(EntityLockService::class)]
#[RunTestsInSeparateProcesses]
final class EntityLockServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试初始化，这里无需特殊设置
    }

    public function testServiceCanBeRetrievedFromContainer(): void
    {
        // 在集成测试中应该能够获取到服务实例
        $service = self::getService(EntityLockService::class);
        $this->assertInstanceOf(EntityLockService::class, $service);
    }

    public function testServiceIsReadOnly(): void
    {
        $reflection = new \ReflectionClass(EntityLockService::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testServiceIsAutoconfigured(): void
    {
        $reflection = new \ReflectionClass(EntityLockService::class);
        $attributes = $reflection->getAttributes();

        $hasAutoconfigure = false;
        foreach ($attributes as $attribute) {
            if ('Symfony\Component\DependencyInjection\Attribute\Autoconfigure' === $attribute->getName()) {
                $hasAutoconfigure = true;
                break;
            }
        }

        $this->assertTrue($hasAutoconfigure, 'EntityLockService should have Autoconfigure attribute');
    }

    public function testLockEntityMethodSignature(): void
    {
        $reflection = new \ReflectionClass(EntityLockService::class);
        $method = $reflection->getMethod('lockEntity');

        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());

        $parameters = $method->getParameters();
        $this->assertEquals('entity', $parameters[0]->getName());
        $this->assertEquals('func', $parameters[1]->getName());
    }

    public function testLockEntitiesMethodSignature(): void
    {
        $reflection = new \ReflectionClass(EntityLockService::class);
        $method = $reflection->getMethod('lockEntities');

        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());

        $parameters = $method->getParameters();
        $this->assertEquals('entities', $parameters[0]->getName());
        $this->assertEquals('func', $parameters[1]->getName());
    }
}
