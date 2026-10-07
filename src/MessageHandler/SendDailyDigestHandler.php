<?php

namespace App\MessageHandler;

use App\Message\SendDailyDigest;
use App\Service\DigestMailer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendDailyDigestHandler
{
    public function __construct(private readonly DigestMailer $digestMailer)
    {
    }

    public function __invoke(SendDailyDigest $message): void
    {
        $this->digestMailer->sendDaily();
    }
}