<?php

namespace AppBundle\Command;

use Exception;
use Swift_Message;
use AppBundle\Entity\AbstractCommand;
use AppBundle\Entity\Zulu;
use AppBundle\Entity\ZuluCommand;
use AppBundle\Service\CommandsHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TurnOffDevicesCommand.
 *
 * This command's purpose is to turn off all devices hooked on to the zulu. You should schedule this command at a point
 * of day where no one will be using the zulu. When there are also classes on the weekend, you might want to make
 * separate schedules for them.
 */
class TurnOffDevicesCommand extends ContainerAwareCommand
{
    /**
     * Configures the command, sets helptext and parameters.
     */
    protected function configure()
    {
        $this->setName('app:zulu:devices:turnoff')->setDescription('Turns all devices off.');
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
        $em   = $this->getContainer()->get('doctrine.orm.entity_manager');
        $text = '';
        $io   = new SymfonyStyle($input, $output);
        $io->title('Geräte ausschalten');
        /* @var Zulu[] $zulus */
        $zulus = $em->getRepository('AppBundle:Zulu')->findAll();
        /** @var AbstractCommand $command */
        $command        = $em->getRepository(ZuluCommand::class)->findOneBy(['name' => 'cmd_shutdownAll']);
        $commandHandler = $this->getContainer()->get('command_handler');
        foreach ($zulus as $zulu) {
            $commandHandler->setZulu($zulu);
            $io->text('Aktueller Status:');
            $status = $commandHandler->getStatusOfZulu();
            $io->text(json_encode($status));
            $text .= sprintf("Raum %s\n", $zulu->getRoom());
            // Add this, when an intelligent method of implementing this function has been accepted. CommandsHandler::statusToHumanReadable($status);
            try {
                if ($status['12'] !== true) {
                    $io->text(sprintf('Geräte im Raum "%s" abstellen.', $zulu->getRoom()));
                    $commandHandler->runCommand($command);
                }
            } catch (Exception $e) {
                $io->text(sprintf('Exception: "%s"', $e->getMessage()));
                $io->text(sprintf('Geräte im Raum "%s" abstellen.', $zulu->getRoom()));
                $commandHandler->runCommand($command);
            }
        }

        $from = $this->getContainer()->getParameter('application_sender_email');
        $to   = $this->getContainer()->getParameter('application_receiver_email');

        $message = Swift_Message::newInstance()->setSubject('Cron message')->setFrom($from)->setTo($to)->setBody($text);
        $this->getContainer()->get('mailer')->send($message);

        return 0;
    }
}
