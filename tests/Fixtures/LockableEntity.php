<?php

namespace Tourze\DoctrineEntityLockBundle\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Tourze\LockServiceBundle\Model\LockEntity;

#[ORM\Entity]
#[ORM\Table(name: 'lockable_entity')]
class LockableEntity implements LockEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function retrieveLockResource(): string
    {
        return 'lockable_entity:' . $this->id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
