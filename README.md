# Doctrine Entity Lock Bundle

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)]()
[![Coverage Status](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)]()

[English](README.md) | [中文](README.zh-CN.md)

This Symfony Bundle provides a simple way to apply distributed locking mechanisms to Doctrine entities for handling concurrent operations.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
  - [Prerequisites](#prerequisites)
  - [Locking Single Entity](#locking-single-entity)
  - [Locking Multiple Entities](#locking-multiple-entities)
- [Testing](#testing)
- [Configuration](#configuration)
  - [Custom Lock Timeout](#custom-lock-timeout)
  - [Custom Lock Store](#custom-lock-store)
- [Dependencies](#dependencies)
- [Advanced Usage](#advanced-usage)
  - [Handling Lock Timeout](#handling-lock-timeout)
  - [Nested Locking](#nested-locking)
  - [Custom Lock Resource Key](#custom-lock-resource-key)
- [License](#license)

## Features

- Apply distributed locks to single entities
- Apply distributed locks to multiple entities at once
- Automatically refresh entity data from database after acquiring lock, ensuring data consistency

## Installation

```bash
composer require tourze/doctrine-entity-lock-bundle
```

Add to your Symfony application's `config/bundles.php`:

```php
Tourze\DoctrineEntityLockBundle\DoctrineEntityLockBundle::class => ['all' => true],
```

## Usage

### Prerequisites

Ensure your entity classes implement the `Tourze\LockServiceBundle\Model\LockEntity` interface:

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

### Locking Single Entity

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
            // Code here executes after acquiring the lock
            // Entity has been automatically refreshed to ensure data consistency
            $user->setName($data['name']);
            // ...
            return $result;
        });
    }
}
```

### Locking Multiple Entities

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
            // Code here executes after acquiring all locks
            // All entities have been automatically refreshed to ensure data consistency
            $from->debit($amount);
            $to->credit($amount);
            // ...
            return $result;
        });
    }
}
```

## Testing

Run tests:

```bash
./vendor/bin/phpunit packages/doctrine-entity-lock-bundle/tests
```

## Configuration

This Bundle uses default configuration, but you can customize lock behavior through the following methods:

### Custom Lock Timeout

You can customize lock timeout by configuring the `LockService` in your service definitions:

```yaml
# config/services.yaml
services:
    Tourze\LockServiceBundle\Service\LockService:
        arguments:
            $defaultTtl: 300 # Default lock time (seconds)
            $maxRetries: 3   # Maximum retry count
```

### Custom Lock Store

By default, locks use Symfony's default lock store. You can configure different storage backends:

```yaml
# config/packages/lock.yaml
framework:
    lock:
        default: redis
        resources:
            redis:
                redis: 'redis://localhost:6379'
```

## Dependencies

This Bundle depends on the following components:

- **doctrine/orm**: ^3.0
- **doctrine/doctrine-bundle**: ^2.13
- **symfony/framework-bundle**: ^7.3
- **symfony/lock**: ^7.3
- **tourze/lock-service-bundle**: Provides distributed locking base services
- **tourze/bundle-dependency**: Automatically manages Bundle dependencies

Development dependencies:
- **phpunit/phpunit**: ^11.5
- **phpstan/phpstan**: ^2.1

## Advanced Usage

### Handling Lock Timeout

When unable to acquire a lock, the service will throw an exception. You can handle this by catching the exception:

```php
use Tourze\LockServiceBundle\Exception\LockAcquisitionException;

try {
    $this->entityLockService->lockEntity($user, function () use ($user) {
        // Handle business logic
    });
} catch (LockAcquisitionException $e) {
    // Handle lock conflict
    throw new \RuntimeException('User is being modified by another process, please try again later');
}
```

### Nested Locking

Support for nested locking of different entities:

```php
$this->entityLockService->lockEntity($order, function () use ($order) {
    // Process order
    
    $this->entityLockService->lockEntity($order->getUser(), function () use ($order) {
        // Process user data simultaneously
    });
});
```

### Custom Lock Resource Key

By implementing the `retrieveLockResource()` method of the `LockEntity` interface, you can customize the lock key:

```php
class Order implements LockEntity
{
    public function retrieveLockResource(): string
    {
        // Use order number as lock key to ensure uniqueness
        return sprintf('order:%s:%s', $this->getOrderNumber(), $this->getId());
    }
}
```

## License

MIT