<?php

namespace App\MessageHandler;

use App\Message\SendWeeklyDigest;
use App\Service\DigestMailer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendWeeklyDigestHandler
{
    public function __construct(private readonly DigestMailer $digestMailer)
    {
    }

    public function __invoke(SendWeeklyDigest $message): void
    {
        $this->digestMailer->sendWeekly();
    }
}