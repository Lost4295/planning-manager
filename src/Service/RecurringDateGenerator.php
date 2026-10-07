<?php

namespace App\Service;

use App\Entity\Date;
use App\Entity\DateScheduler;

class RecurringDateGenerator
{
    public const MAX_OCCURRENCES = 366;

    /**
     * Crée les occurrences suivant $first (qui reste l'occurrence n°0 de la série).
     *
     * @return Date[]
     */
    public function generate(Date $first, DateScheduler $scheduler): array
    {
        $every = $scheduler->getRepeatEvery();
        if (!$scheduler->getRepeatable() || null === $every) {
            return [];
        }

        $duration = $first->getEndDate()->getTimestamp() - $first->getStartDate()->getTimestamp();
        $count = $scheduler->getOccurrences();
        $until = $scheduler->getRepeatUntil();
        $dates = [];

        for ($i = 1; $i < self::MAX_OCCURRENCES; $i++) {
            if (null !== $count && $i >= $count) {
                break;
            }
            $start = $every->shift($first->getStartDate(), $i);
            if (null !== $until && $start->format('Y-m-d') > $until->format('Y-m-d')) {
                break;
            }

            $date = (new Date())
                ->setTitle($first->getTitle())
                ->setDescription((string) $first->getDescription())
                ->setColor($first->getColor())
                ->setImportant($first->isImportant())
                ->setIsFromMe($first->isFromMe())
                ->setStartDate(\DateTime::createFromImmutable($start))
                ->setEndDate(\DateTime::createFromImmutable($start->modify(sprintf('+%d seconds', $duration))));
            $scheduler->addDateSelected($date);
            $dates[] = $date;
        }

        return $dates;
    }
}
