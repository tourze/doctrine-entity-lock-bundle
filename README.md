# Doctrine Entity Lock Bundle

这个 Symfony Bundle 提供了一种简单的方式来为 Doctrine 实体应用分布式锁机制，可以用于处理并发操作。

## 功能

- 为单个实体应用分布式锁
- 为多个实体一次性应用分布式锁
- 在获取锁后自动从数据库刷新实体数据，确保数据的一致性

## 安装

```bash
composer require tourze/doctrine-entity-lock-bundle
```

在 Symfony 应用的 `config/bundles.php` 中添加:

```php
Tourze\DoctrineEntityLockBundle\DoctrineEntityLockBundle::class => ['all' => true],
```

## 使用方法

### 前提条件

确保你的实体类实现了 `Tourze\LockServiceBundle\Model\LockEntity` 接口:

```php
use Tourze\LockServiceBundle\Model\LockEntity;

class User implements LockEntity
{
    // ...

    public function retrieveLockResource(): string
    {
        return 'user:' . $this->id;
    }
}
```

### 锁定单个实体

```php
use Tourze\DoctrineEntityLockBundle\Service\EntityLockService;

class UserService
{
    public function __construct(private readonly EntityLockService $entityLockService)
    {
    }

    public function updateUser(User $user, array $data): void
    {
        $this->entityLockService->lockEntity($user, function () use ($user, $data) {
            // 这里的代码在获取锁后执行
            // 实体已经被自动刷新，确保数据的一致性
            $user->setName($data['name']);
            // ...
            return $result;
        });
    }
}
```

### 锁定多个实体

```php
use Tourze\DoctrineEntityLockBundle\Service\EntityLockService;

class TransferService
{
    public function __construct(private readonly EntityLockService $entityLockService)
    {
    }

    public function transfer(Account $from, Account $to, float $amount): void
    {
        $this->entityLockService->lockEntities([$from, $to], function () use ($from, $to, $amount) {
            // 这里的代码在获取全部锁后执行
            // 所有实体都已经被自动刷新，确保数据的一致性
            $from->debit($amount);
            $to->credit($amount);
            // ...
            return $result;
        });
    }
}
```

## 测试

运行测试：

```bash
./vendor/bin/phpunit packages/doctrine-entity-lock-bundle/tests
```
