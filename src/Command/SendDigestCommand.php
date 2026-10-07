<?php

namespace App\Command;

use App\Service\DigestMailer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:mail:digest', description: 'Envoie immédiatement le récapitulatif quotidien ou hebdomadaire')]
class SendDigestCommand extends Command
{
    public function __construct(private readonly DigestMailer $digestMailer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('type', InputArgument::REQUIRED, 'daily ou weekly');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');
        if (!in_array($type, ['daily', 'weekly'], true)) {
            $io->error('Le type doit être "daily" ou "weekly".');

            return Command::INVALID;
        }

        $sent = 'daily' === $type ? $this->digestMailer->sendDaily() : $this->digestMailer->sendWeekly();
        $io->success(sprintf('%d mail(s) envoyé(s).', $sent));

        return Command::SUCCESS;
    }
}