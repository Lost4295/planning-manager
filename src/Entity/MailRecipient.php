<?php

namespace App\Entity;

use App\Repository\MailRecipientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MailRecipientRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_MAIL_RECIPIENT_EMAIL', fields: ['email'])]
class MailRecipient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $name = null;

    #[ORM\Column]
    private bool $daily = true;

    #[ORM\Column]
    private bool $weekly = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function isDaily(): bool
    {
        return $this->daily;
    }

    public function setDaily(bool $daily): static
    {
        $this->daily = $daily;
        return $this;
    }

    public function isWeekly(): bool
    {
        return $this->weekly;
    }

    public function setWeekly(bool $weekly): static
    {
        $this->weekly = $weekly;
        return $this;
    }
}