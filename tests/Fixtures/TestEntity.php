<?php

namespace Tourze\DoctrineEntityLockBundle\Tests\Fixtures;

use Tourze\LockServiceBundle\Model\LockEntity;

class TestEntity implements LockEntity
{
    private string $id;

    public function __construct(string $id = 'test-entity-id')
    {
        $this->id = $id;
    }

    /**
     * 获取锁资源标识
     */
    public function retrieveLockResource(): string
    {
        return 'entity:' . $this->id;
    }

    /**
     * 获取实体ID
     */
    public function getId(): string
    {
        return $this->id;
    }
}
