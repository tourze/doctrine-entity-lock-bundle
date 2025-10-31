# Doctrine Entity Lock Bundle

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)]()
[![Coverage Status](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)]()

[English](README.md) | [中文](README.zh-CN.md)

这个 Symfony Bundle 提供了一种简单的方式来为 Doctrine 实体应用分布式锁机制，可以用于处理并发操作。

## 目录

- [功能](#功能)
- [安装](#安装)
- [使用方法](#使用方法)
  - [前提条件](#前提条件)
  - [锁定单个实体](#锁定单个实体)
  - [锁定多个实体](#锁定多个实体)
- [测试](#测试)
- [配置](#配置)
  - [自定义锁超时时间](#自定义锁超时时间)
  - [自定义锁存储](#自定义锁存储)
- [依赖](#依赖)
- [高级用法](#高级用法)
  - [处理锁定超时](#处理锁定超时)
  - [嵌套锁定](#嵌套锁定)
  - [自定义锁资源键](#自定义锁资源键)
- [许可证](#许可证)

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

## 配置

这个 Bundle 使用了默认的配置，但你可以通过以下方式自定义锁的行为：

### 自定义锁超时时间

在你的服务定义中，可以通过配置 `LockService` 来自定义锁的超时时间：

```yaml
# config/services.yaml
services:
    Tourze\LockServiceBundle\Service\LockService:
        arguments:
            $defaultTtl: 300 # 默认锁定时间（秒）
            $maxRetries: 3   # 最大重试次数
```

### 自定义锁存储

默认情况下，锁使用 Symfony 的默认锁存储。你可以配置不同的存储后端：

```yaml
# config/packages/lock.yaml
framework:
    lock:
        default: redis
        resources:
            redis:
                redis: 'redis://localhost:6379'
```

## 依赖

这个 Bundle 依赖以下组件：

- **doctrine/orm**: ^3.0
- **doctrine/doctrine-bundle**: ^2.13
- **symfony/framework-bundle**: ^6.4
- **symfony/lock**: ^6.4
- **tourze/lock-service-bundle**: 提供分布式锁的基础服务
- **tourze/bundle-dependency**: 自动管理 Bundle 依赖关系

开发依赖：
- **phpunit/phpunit**: ^10.0
- **phpstan/phpstan**: ^2.1

## 高级用法

### 处理锁定超时

当无法获取锁时，服务会抛出异常。你可以通过捕获异常来处理这种情况：

```php
use Symfony\Component\Lock\Exception\LockConflictedException;

try {
    $this->entityLockService->lockEntity($user, function () use ($user) {
        // 处理业务逻辑
    });
} catch (LockConflictedException $e) {
    // 处理锁冲突
    throw new \RuntimeException('用户正在被其他进程修改，请稍后重试');
}
```

### 嵌套锁定

支持嵌套锁定不同的实体：

```php
$this->entityLockService->lockEntity($order, function () use ($order) {
    // 处理订单
    
    $this->entityLockService->lockEntity($order->getUser(), function () use ($order) {
        // 同时处理用户数据
    });
});
```

### 自定义锁资源键

通过实现 `LockEntity` 接口的 `retrieveLockResource()` 方法，你可以自定义锁的键：

```php
class Order implements LockEntity
{
    public function retrieveLockResource(): string
    {
        // 使用订单号作为锁键，确保唯一性
        return sprintf('order:%s:%s', $this->getOrderNumber(), $this->getId());
    }
}
```

## 许可证

MIT
