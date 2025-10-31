<?php

declare(strict_types=1);

namespace Tourze\DoctrineEntityLockBundle\Tests\Service;

use BizUserBundle\Entity\BizUser;
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
    }

    private function getEntityLockService(): EntityLockService
    {
        return self::getService(EntityLockService::class);
    }

    /**
     * 测试锁定单个实体的正常流程
     */
    public function testLockEntityWithValidEntityCallsRefreshAndExecutesCallback(): void
    {
        // 准备测试数据
        $lockService = $this->getEntityLockService();
        /** @var BizUser $entity */
        $entity = $this->createNormalUser('test@example.com', 'password123');
        $callbackExecuted = false;
        $callbackResult = 'callback-result';
        $callback = function () use (&$callbackExecuted, $callbackResult) {
            $callbackExecuted = true;

            return $callbackResult;
        };

        // 执行测试
        $result = $lockService->lockEntity($entity, $callback);

        // 验证结果
        $this->assertTrue($callbackExecuted, '回调函数应该被执行');
        $this->assertEquals($callbackResult, $result, '回调函数的结果应该被返回');
    }

    /**
     * 测试锁定单个实体的基本功能
     */
    public function testLockEntityBasicFunctionality(): void
    {
        $lockService = $this->getEntityLockService();
        /** @var BizUser $entity */
        $entity = $this->createNormalUser('test2@example.com', 'password123');
        $callbackExecuted = false;

        $result = $lockService->lockEntity($entity, function () use (&$callbackExecuted) {
            $callbackExecuted = true;

            return 'success';
        });

        $this->assertTrue($callbackExecuted, '回调函数应该被执行');
        $this->assertEquals('success', $result, '回调函数的结果应该被返回');
    }

    /**
     * 测试锁定多个实体的正常流程
     */
    public function testLockEntitiesWithValidEntitiesCallsRefreshForEachEntityAndExecutesCallback(): void
    {
        // 准备测试数据
        $lockService = $this->getEntityLockService();
        /** @var BizUser $entity1 */
        $entity1 = $this->createNormalUser('user1@example.com', 'password123');
        /** @var BizUser $entity2 */
        $entity2 = $this->createNormalUser('user2@example.com', 'password123');
        $entities = [$entity1, $entity2];

        $callbackExecuted = false;
        $callbackResult = 'callback-result';
        $callback = function () use (&$callbackExecuted, $callbackResult) {
            $callbackExecuted = true;

            return $callbackResult;
        };

        // 执行测试
        $result = $lockService->lockEntities($entities, $callback);

        // 验证结果
        $this->assertTrue($callbackExecuted, '回调函数应该被执行');
        $this->assertEquals($callbackResult, $result, '回调函数的结果应该被返回');
    }

    /**
     * 测试锁定空实体数组的情况
     */
    public function testLockEntitiesWithEmptyArraySkipsRefreshAndExecutesCallback(): void
    {
        // 准备测试数据
        $lockService = $this->getEntityLockService();
        $entities = [];

        $callbackExecuted = false;
        $callbackResult = 'callback-result';
        $callback = function () use (&$callbackExecuted, $callbackResult) {
            $callbackExecuted = true;

            return $callbackResult;
        };

        // 执行测试
        $result = $lockService->lockEntities($entities, $callback);

        // 验证结果
        $this->assertTrue($callbackExecuted, '回调函数应该被执行');
        $this->assertEquals($callbackResult, $result, '回调函数的结果应该被返回');
    }

    /**
     * 测试锁定多个实体的基本功能
     */
    public function testLockEntitiesBasicFunctionality(): void
    {
        $lockService = $this->getEntityLockService();
        /** @var BizUser $entity1 */
        $entity1 = $this->createNormalUser('user3@example.com', 'password123');
        /** @var BizUser $entity2 */
        $entity2 = $this->createNormalUser('user4@example.com', 'password123');
        $entities = [$entity1, $entity2];
        $callbackExecuted = false;

        $result = $lockService->lockEntities($entities, function () use (&$callbackExecuted) {
            $callbackExecuted = true;

            return 'multi-entity-success';
        });

        $this->assertTrue($callbackExecuted, '回调函数应该被执行');
        $this->assertEquals('multi-entity-success', $result, '回调函数的结果应该被返回');
    }
}
