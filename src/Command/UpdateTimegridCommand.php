<?php

namespace App\Command;

use App\Service\WebUntisHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UpdateTimegridCommand.
 *
 * This command updates the timegrid that is stored in the database. The timegrid can and will be used to check when
 * the end of certain lessons appear.
 */
class UpdateTimegridCommand extends Command
{
    private $webUntisHandler;

    public function __construct(WebUntisHandler $webUntisHandler)
    {
        parent::__construct(null);

        $this->webUntisHandler = $webUntisHandler;
    }

    protected function configure(): void
    {
        $this->setName('app:update:timegrid')->setDescription('Updates timegrid in database.');
    }

    /**
     * This is the entry-point when running the command from the CLI.
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Timegrids erneuern');
        $io->section('Timegrids');
        $io->text('Alte Einträge werden gelöscht....');
        $this->webUntisHandler->login()->updateTimegrid();
        $io->text('Einträge wurden erneuert.');

        return 0;
    }
}
