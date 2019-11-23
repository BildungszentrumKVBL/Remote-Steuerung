<?php

namespace App\Command;

use DateTime;
use App\Entity\Log;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanDatabaseCommand.
 *
 * This command cleans various tables in the database. This ensures that the storage won't overflow over time and
 * improve database performance.
 */
class CleanDatabaseCommand extends ContainerAwareCommand
{
    /**
     * Configures the command, sets helptext and parameters.
     */
    protected function configure()
    {
        $this->setName('app:clean:database')->setDescription('Entfernt Logeinträge, welche älter als 1 Monat sind.');
    }

    /**
     * This is the entry-point when running the command from the CLI.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Datenbank Säuberungsroutine');
        $io->section('Logs');

        $this->displayTotalLogs($io);
        $this->clearOldLogs($io);

        return 0;
    }

    /**
     * Prints a formatted output of the total amount of logs in the database.
     *
     * @param OutputInterface $io
     */
    protected function displayTotalLogs(OutputInterface $io)
    {
        $queryBuilder = $this->getContainer()->get('doctrine.orm.entity_manager')->createQueryBuilder();
        $amount       = $queryBuilder->select('count(log.id)')->from(Log::class, 'log')->getQuery()->getSingleScalarResult();
        $io->writeln(sprintf('%d Logeinträge insgesamt gefunden.', $amount));
    }

    /**
     * Deletes logs older than 1 month from the database.
     *
     * @param OutputInterface $io
     */
    protected function clearOldLogs(OutputInterface $io)
    {
        $date = new DateTime('-1 month');
        $em   = $this->getContainer()->get('doctrine.orm.entity_manager');
        $logs = $em->createQueryBuilder()->select('l')->from(Log::class, 'l')->where('l.dateTime <= :datetime')->setParameter(':datetime', $date)->getQuery()->getResult();

        foreach ($logs as $log) {
            $em->remove($log);
        }
        $em->flush();

        $msg = sprintf('%d Logeinträge aus der Datenbank entfernt.', count($logs));

        $log = new Log($msg, Log::LEVEL_SYSTEM);
        $em->persist($log);
        $em->flush();

        $io->writeln($msg);
    }
}
