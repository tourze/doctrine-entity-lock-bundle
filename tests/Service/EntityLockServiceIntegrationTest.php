<?php

namespace Tourze\DoctrineEntityLockBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\DoctrineEntityLockBundle\DoctrineEntityLockBundle;
use Tourze\DoctrineEntityLockBundle\Service\EntityLockService;
use Tourze\DoctrineEntityLockBundle\Tests\Fixtures\LockableEntity;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;

/**
 * @covers \Tourze\DoctrineEntityLockBundle\Service\EntityLockService
 */
class EntityLockServiceIntegrationTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;

    private ?EntityLockService $entityLockService;

    protected static function createKernel(array $options = []): KernelInterface
    {
        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new IntegrationTestKernel(
            $env,
            $debug,
            [
                DoctrineEntityLockBundle::class => ['all' => true],
            ],
            [
                'Tourze\DoctrineEntityLockBundle\Tests\Fixtures' => __DIR__ . '/../Fixtures',
            ]
        );
    }

    /**
     * @see EntityLockService::lockEntity()
     */
    public function test_lockEntity_withValidEntity_refreshesAndAllowsModification(): void
    {
        // Arrange
        $entity = new LockableEntity('entity-1');
        $entity->setName('initial name');
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $managedEntity = $this->entityManager->find(LockableEntity::class, 'entity-1');
        self::assertNotNull($managedEntity);

        // Act
        $callbackExecuted = false;
        $this->entityLockService->lockEntity($managedEntity, function () use ($managedEntity, &$callbackExecuted) {
            $managedEntity->setName('updated name');
            $callbackExecuted = true;
        });
        $this->entityManager->flush();

        // Assert
        self::assertTrue($callbackExecuted, '回调函数应该被执行');
        $this->entityManager->clear();
        $refreshedEntity = $this->entityManager->find(LockableEntity::class, 'entity-1');
        self::assertEquals('updated name', $refreshedEntity->getName());
    }

    /**
     * @see EntityLockService::lockEntity()
     */
    public function test_lockEntity_withStaleData_refreshesToLatestState(): void
    {
        // Arrange
        $entity = new LockableEntity('entity-2');
        $entity->setName('initial name');
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->entityManager->getConnection()->executeStatement("UPDATE lockable_entity SET name = 'updated in db' WHERE id = 'entity-2'");
        $this->entityManager->clear();

        $managedEntity = $this->entityManager->find(LockableEntity::class, 'entity-2');
        self::assertNotNull($managedEntity);

        // Act & Assert
        $this->entityLockService->lockEntity($managedEntity, function () use ($managedEntity) {
            self::assertEquals('updated in db', $managedEntity->getName(), '实体应该在锁内被刷新');
        });
    }

    /**
     * @see EntityLockService::lockEntities()
     */
    public function test_lockEntities_withValidEntities_refreshesAndAllowsModification(): void
    {
        // Arrange
        $entity1 = new LockableEntity('entity-3');
        $entity1->setName('initial name 1');
        $this->entityManager->persist($entity1);

        $entity2 = new LockableEntity('entity-4');
        $entity2->setName('initial name 2');
        $this->entityManager->persist($entity2);

        $this->entityManager->flush();

        $this->entityManager->getConnection()->executeStatement("UPDATE lockable_entity SET name = 'updated in db 1' WHERE id = 'entity-3'");
        $this->entityManager->getConnection()->executeStatement("UPDATE lockable_entity SET name = 'updated in db 2' WHERE id = 'entity-4'");
        $this->entityManager->clear();

        $managedEntity1 = $this->entityManager->find(LockableEntity::class, 'entity-3');
        $managedEntity2 = $this->entityManager->find(LockableEntity::class, 'entity-4');

        // Act
        $callbackExecuted = false;
        $this->entityLockService->lockEntities([$managedEntity1, $managedEntity2], function () use ($managedEntity1, $managedEntity2, &$callbackExecuted) {
            self::assertEquals('updated in db 1', $managedEntity1->getName());
            self::assertEquals('updated in db 2', $managedEntity2->getName());

            $managedEntity1->setName('final name 1');
            $managedEntity2->setName('final name 2');
            $callbackExecuted = true;
        });
        $this->entityManager->flush();

        // Assert
        self::assertTrue($callbackExecuted, '回调函数应该被执行');
        $this->entityManager->clear();
        $refreshedEntity1 = $this->entityManager->find(LockableEntity::class, 'entity-3');
        self::assertEquals('final name 1', $refreshedEntity1->getName());
        $refreshedEntity2 = $this->entityManager->find(LockableEntity::class, 'entity-4');
        self::assertEquals('final name 2', $refreshedEntity2->getName());
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->entityLockService = $container->get(EntityLockService::class);
    }

    protected function tearDown(): void
    {
        $this->entityManager = null;
        $this->entityLockService = null;
        parent::tearDown();
    }
}
