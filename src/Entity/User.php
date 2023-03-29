<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['list', 'get'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['list', 'get'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(['list', 'get'])]
    private ?Group $userGroup = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUserGroup(): ?Group
    {
        return $this->userGroup;
    }

    public function setUserGroup(?Group $userGroup): self
    {
        $this->userGroup = $userGroup;

        return $this;
    }
}
