<?php

namespace App;

use App\Message\SendDailyDigest;
use App\Message\SendWeeklyDigest;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule]
class Schedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): SymfonySchedule
    {
        return (new SymfonySchedule())
            ->stateful($this->cache) // ensure missed tasks are executed
            ->processOnlyLastMissedRun(true) // ensure only last missed task is run

            ->add(RecurringMessage::cron('0 7 * * *', new SendDailyDigest(), new \DateTimeZone('Europe/Paris')))
            ->add(RecurringMessage::cron('0 7 * * 1', new SendWeeklyDigest(), new \DateTimeZone('Europe/Paris')))
        ;
    }
}
