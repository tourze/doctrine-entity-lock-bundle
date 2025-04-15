<?php

namespace Tourze\DoctrineEntityLockBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\LockServiceBundle\Model\LockEntity;
use Tourze\LockServiceBundle\Service\LockService;

class EntityLockService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LockService $lockService,
    )
    {
    }

    /**
     * 锁定单个实体，从数据库读取最新数据，然后执行回调
     */
    public function lockEntity(LockEntity $entity, callable $func): mixed
    {
        return $this->lockService->blockingRun($entity, function () use ($entity, $func) {
            // 当我们拿到锁，我们先从数据库拉最新的数据过来，避免多进程同时读取出现问题
            $this->entityManager->refresh($entity);

            return call_user_func_array($func, []);
        });
    }

    /**
     * 锁定多个实体，从数据库读取最新数据，然后执行回调
     *
     * @param LockEntity[] $entities
     */
    public function lockEntities(array $entities, callable $func): mixed
    {
        return $this->lockService->blockingRun($entities, function () use ($entities, $func) {
            // 当我们拿到锁，我们先从数据库拉最新的数据过来，避免多进程同时读取出现问题
            foreach ($entities as $entity) {
                $this->entityManager->refresh($entity);
            }

            return call_user_func_array($func, []);
        });
    }
}
