<?php

namespace App\Entity;

use App\Enum\RepeatableEnum;
use App\Repository\DateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DateRepository::class)]
class Date
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?\DateTime $start_date = null;

    #[ORM\Column]
    private ?\DateTime $end_date = null;

    #[ORM\Column(length: 6)]
    private ?string $color = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;


    #[ORM\Column]
    private bool $isFromMe = false;

    #[ORM\ManyToOne(inversedBy: 'dateSelected')]
    private ?DateScheduler $dateScheduler = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTime $start_date): static
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTime $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }


    public function isFromMe(): bool
    {
        return $this->isFromMe;
    }

    public function setIsFromMe(bool $isFromMe): Date
    {
        $this->isFromMe = $isFromMe;
        return $this;
    }

    public function getDateScheduler(): ?DateScheduler
    {
        return $this->dateScheduler;
    }

    public function setDateScheduler(?DateScheduler $dateScheduler): static
    {
        $this->dateScheduler = $dateScheduler;

        return $this;
    }



}
