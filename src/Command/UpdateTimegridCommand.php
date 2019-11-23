<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateTimegridCommand.
 *
 * This command updates the timegrid that is stored in the database. The timegrid can and will be used to check when
 * the end of certain lessons appear.
 */
class UpdateTimegridCommand extends ContainerAwareCommand
{
    /**
     * Configures the command, sets helptext and parameters.
     */
    protected function configure()
    {
        $this->setName('app:update:timegrid')->setDescription('Updates timegrid in database.');
    }

    /**
     * This is the entry-point when running the command from the CLI.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Timegrids erneuern');
        $io->section('Timegrids');
        $io->text('Alte Einträge werden gelöscht....');
        $this->getContainer()->get('app.webuntis.handler')->login()->updateTimegrid();
        $io->text('Einträge wurden erneuert.');

        return 0;
    }
}
