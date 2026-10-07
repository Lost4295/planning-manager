<?php

namespace App\Service;

use App\Entity\Date;
use App\Repository\DateRepository;
use App\Repository\MailRecipientRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class DigestMailer
{
    private const DAYS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    public function __construct(
        private readonly DateRepository $dates,
        private readonly MailRecipientRepository $recipients,
        private readonly MailerInterface $mailer,
        #[Autowire('%env(MAIL_SENDER_EMAIL)%')]
        private readonly string $sender,
    ) {
    }

    /** @return int nombre de mails envoyés */
    public function sendDaily(?\DateTimeImmutable $now = null): int
    {
        $start = ($now ?? new \DateTimeImmutable())->setTime(0, 0);

        return $this->send(false, $start, $start->modify('+1 day'));
    }

    /** @return int nombre de mails envoyés */
    public function sendWeekly(?\DateTimeImmutable $now = null): int
    {
        $start = ($now ?? new \DateTimeImmutable())->modify('monday this week')->setTime(0, 0);

        return $this->send(true, $start, $start->modify('+7 days'));
    }

    private function send(bool $weekly, \DateTimeImmutable $start, \DateTimeImmutable $end): int
    {
        $recipients = $this->recipients->findForDigest($weekly);
        if (!$recipients) {
            return 0;
        }

        $days = $this->buildDays($start, $end);
        if (!$days) {
            return 0;
        }

        $last = $end->modify('-1 day');
        $subject = $weekly
            ? sprintf('Vos événements de la semaine du %s au %s', $start->format('d/m'), $last->format('d/m'))
            : sprintf('Vos événements du %s', $this->dayLabel($start));

        $count = 0;
        foreach ($recipients as $recipient) {
            $email = (new TemplatedEmail())
                ->from($this->sender)
                ->to(new Address($recipient->getEmail(), (string) $recipient->getName()))
                ->subject($subject)
                ->htmlTemplate('email/digest.html.twig')
                ->context(['weekly' => $weekly, 'days' => $days, 'recipient' => $recipient, 'subject' => $subject]);
            $this->mailer->send($email);
            ++$count;
        }

        return $count;
    }

    /** @return list<array{label: string, events: list<array<string, mixed>>}> */
    private function buildDays(\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $days = [];
        /** @var Date $date */
        foreach ($this->dates->findOverlapping($start, $end) as $date) {
            $from = \DateTimeImmutable::createFromInterface($date->getStartDate());
            $to = \DateTimeImmutable::createFromInterface($date->getEndDate());
            $key = max($from, $start)->format('Y-m-d');
            $days[$key]['label'] ??= $this->dayLabel(max($from, $start));
            $days[$key]['events'][] = [
                'title' => $date->getTitle(),
                'description' => $date->getDescription(),
                'important' => $date->isImportant(),
                'color' => $date->getColor(),
                'start' => $from,
                'end' => $to,
                'sameDay' => $from->format('Y-m-d') === $to->format('Y-m-d'),
                'duration' => $this->formatDuration($to->getTimestamp() - $from->getTimestamp()),
            ];
        }
        ksort($days);

        return array_values($days);
    }

    private function dayLabel(\DateTimeImmutable $d): string
    {
        return ucfirst(self::DAYS[(int) $d->format('N') - 1]).' '.$d->format('d/m/Y');
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = intdiv(max(0, $seconds), 60);
        $parts = [];
        if ($days = intdiv($minutes, 1440)) {
            $parts[] = $days.' j';
        }
        if ($hours = intdiv($minutes % 1440, 60)) {
            $parts[] = $hours.' h';
        }
        if ($mins = $minutes % 60) {
            $parts[] = $mins.' min';
        }

        return $parts ? implode(' ', $parts) : '0 min';
    }
}