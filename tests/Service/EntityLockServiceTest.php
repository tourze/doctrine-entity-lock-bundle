<?php

namespace Tourze\DoctrineEntityLockBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tourze\DoctrineEntityLockBundle\Service\EntityLockService;
use Tourze\DoctrineEntityLockBundle\Tests\Fixtures\TestEntity;
use Tourze\LockServiceBundle\Service\LockService;

class EntityLockServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private LockService $lockService;
    private EntityLockService $entityLockService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->lockService = $this->createMock(LockService::class);
        $this->entityLockService = new EntityLockService(
            $this->entityManager,
            $this->lockService
        );
    }

    /**
     * 测试锁定单个实体的正常流程
     */
    public function testLockEntity_withValidEntity_callsRefreshAndExecutesCallback(): void
    {
        // 准备测试数据
        $entity = new TestEntity();
        $callbackExecuted = false;
        $callbackResult = 'callback-result';
        $callback = function () use (&$callbackExecuted, $callbackResult) {
            $callbackExecuted = true;
            return $callbackResult;
        };

        // 设置模拟对象的期望行为
        // 因为回调会在 blockingRun 内部执行，所以需要在调用 blockingRun 之前设置 refresh 的期望
        $this->entityManager
            ->expects($this->once())
            ->method('refresh')
            ->with($entity);

        $this->lockService
            ->expects($this->once())
            ->method('blockingRun')
            ->with($entity, $this->anything())
            ->willReturnCallback(function ($entity, $callback) {
                return $callback();
            });

        // 执行测试
        $result = $this->entityLockService->lockEntity($entity, $callback);

        // 验证结果
        $this->assertTrue($callbackExecuted, '回调函数应该被执行');
        $this->assertEquals($callbackResult, $result, '回调函数的结果应该被返回');
    }

    /**
     * 测试锁定单个实体时遇到异常的情况
     */
    public function testLockEntity_whenLockServiceThrowsException_propagatesException(): void
    {
        // 准备测试数据
        $entity = new TestEntity();
        $callback = function () {
            return 'result';
        };
        $exception = new RuntimeException('锁定错误');

        // 设置模拟对象的期望行为
        $this->lockService
            ->expects($this->once())
            ->method('blockingRun')
            ->with($entity, $this->anything())
            ->willThrowException($exception);

        // 验证异常被正确传播
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('锁定错误');

        // 执行测试
        $this->entityLockService->lockEntity($entity, $callback);
    }

    /**
     * 测试锁定多个实体的正常流程
     */
    public function testLockEntities_withValidEntities_callsRefreshForEachEntityAndExecutesCallback(): void
    {
        // 准备测试数据
        $entity1 = new TestEntity('entity-1');
        $entity2 = new TestEntity('entity-2');
        $entities = [$entity1, $entity2];

        $callbackExecuted = false;
        $callbackResult = 'callback-result';
        $callback = function () use (&$callbackExecuted, $callbackResult) {
            $callbackExecuted = true;
            return $callbackResult;
        };

        // 设置模拟对象的期望行为
        // 对每个实体都需要调用一次 refresh
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('refresh')
            ->withAnyParameters();

        $this->lockService
            ->expects($this->once())
            ->method('blockingRun')
            ->with($entities, $this->anything())
            ->willReturnCallback(function ($entities, $callback) {
                return $callback();
            });

        // 执行测试
        $result = $this->entityLockService->lockEntities($entities, $callback);

        // 验证结果
        $this->assertTrue($callbackExecuted, '回调函数应该被执行');
        $this->assertEquals($callbackResult, $result, '回调函数的结果应该被返回');
    }

    /**
     * 测试锁定空实体数组的情况
     */
    public function testLockEntities_withEmptyArray_skipsRefreshAndExecutesCallback(): void
    {
        // 准备测试数据
        $entities = [];

        $callbackExecuted = false;
        $callbackResult = 'callback-result';
        $callback = function () use (&$callbackExecuted, $callbackResult) {
            $callbackExecuted = true;
            return $callbackResult;
        };

        // 设置模拟对象的期望行为
        $this->entityManager
            ->expects($this->never())
            ->method('refresh');

        $this->lockService
            ->expects($this->once())
            ->method('blockingRun')
            ->with($entities, $this->anything())
            ->willReturnCallback(function ($entities, $callback) {
                return $callback();
            });

        // 执行测试
        $result = $this->entityLockService->lockEntities($entities, $callback);

        // 验证结果
        $this->assertTrue($callbackExecuted, '回调函数应该被执行');
        $this->assertEquals($callbackResult, $result, '回调函数的结果应该被返回');
    }

    /**
     * 测试锁定多个实体时遇到异常的情况
     */
    public function testLockEntities_whenLockServiceThrowsException_propagatesException(): void
    {
        // 准备测试数据
        $entity1 = new TestEntity('entity-1');
        $entity2 = new TestEntity('entity-2');
        $entities = [$entity1, $entity2];

        $callback = function () {
            return 'result';
        };
        $exception = new RuntimeException('锁定错误');

        // 设置模拟对象的期望行为
        $this->lockService
            ->expects($this->once())
            ->method('blockingRun')
            ->with($entities, $this->anything())
            ->willThrowException($exception);

        // 验证异常被正确传播
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('锁定错误');

        // 执行测试
        $this->entityLockService->lockEntities($entities, $callback);
    }
}
