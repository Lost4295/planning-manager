<?php

namespace App\Entity;

use App\Enum\RepeatableEnum;
use App\Repository\DateSchedulerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DateSchedulerRepository::class)]
class DateScheduler
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column]
    private ?bool $repeatable = null;

    #[ORM\Column(type: 'integer', nullable: true, enumType: RepeatableEnum::class)]
    private ?RepeatableEnum $repeat_every = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $repeatUntil = null;

    #[ORM\Column(nullable: true)]
    private ?int $occurrences = null;


    /**
     * @var Collection<int, Date>
     */
    #[ORM\OneToMany(targetEntity: Date::class, mappedBy: 'dateScheduler')]
    private Collection $dateSelected;

    public function __construct()
    {
        $this->dateSelected = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepeatable(): ?bool
    {
        return $this->repeatable;
    }

    public function setRepeatable(?bool $repeatable): DateScheduler
    {
        $this->repeatable = $repeatable;
        return $this;
    }

    public function getRepeatEvery(): ?RepeatableEnum
    {
        return $this->repeat_every;
    }

    public function setRepeatEvery(?RepeatableEnum $repeat_every): DateScheduler
    {
        $this->repeat_every = $repeat_every;
        return $this;
    }


    public function getRepeatUntil(): ?\DateTime
    {
        return $this->repeatUntil;
    }

    public function setRepeatUntil(?\DateTime $repeatUntil): static
    {
        $this->repeatUntil = $repeatUntil;
        return $this;
    }

    public function getOccurrences(): ?int
    {
        return $this->occurrences;
    }

    public function setOccurrences(?int $occurrences): static
    {
        $this->occurrences = $occurrences;
        return $this;
    }

    /**
     * @return Collection<int, Date>
     */
    public function getDateSelected(): Collection
    {
        return $this->dateSelected;
    }

    public function addDateSelected(Date $dateSelected): static
    {
        if (!$this->dateSelected->contains($dateSelected)) {
            $this->dateSelected->add($dateSelected);
            $dateSelected->setDateScheduler($this);
        }

        return $this;
    }

    public function removeDateSelected(Date $dateSelected): static
    {
        if ($this->dateSelected->removeElement($dateSelected)) {
            // set the owning side to null (unless already changed)
            if ($dateSelected->getDateScheduler() === $this) {
                $dateSelected->setDateScheduler(null);
            }
        }

        return $this;
    }
}
