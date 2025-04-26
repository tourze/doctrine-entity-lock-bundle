<?php

namespace Tourze\DoctrineEntityLockBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\DoctrineEntityLockBundle\DependencyInjection\DoctrineEntityLockExtension;
use Tourze\DoctrineEntityLockBundle\Service\EntityLockService;

class DoctrineEntityLockExtensionTest extends TestCase
{
    /**
     * 测试扩展加载正常流程
     */
    public function testLoad_registerServiceDefinitions(): void
    {
        // 创建容器
        $container = new ContainerBuilder();

        // 创建扩展并加载配置
        $extension = new DoctrineEntityLockExtension();
        $extension->load([], $container);

        // 验证服务定义是否正确加载
        $this->assertTrue($container->hasDefinition(EntityLockService::class), 'EntityLockService应该被注册到容器中');

        // 验证服务是否配置了自动装配
        $definition = $container->getDefinition(EntityLockService::class);
        $this->assertTrue($definition->isAutowired(), 'EntityLockService应该配置为自动装配');
        $this->assertTrue($definition->isAutoconfigured(), 'EntityLockService应该配置为自动配置');
    }
}
