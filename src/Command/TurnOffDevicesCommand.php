<?php

namespace App\Command;

use App\Entity\AbstractCommand;
use App\Entity\Zulu;
use App\Entity\ZuluCommand;
use App\Service\CommandsHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Swift_Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class TurnOffDevicesCommand.
 *
 * This command's purpose is to turn off all devices hooked on to the zulu. You should schedule this command at a point
 * of day where no one will be using the zulu. When there are also classes on the weekend, you might want to make
 * separate schedules for them.
 */
class TurnOffDevicesCommand extends Command
{
    private $em;
    private $commandHandler;
    private $senderMail;
    private $receiverMail;
    private $mailer;

    /**
     * TurnOffDevicesCommand constructor.
     */
    public function __construct(EntityManagerInterface $em, CommandsHandler $commandHandler, \Swift_Mailer $mailer, string $senderMail, string $receiverMail)
    {
        parent::__construct(null);

        $this->em             = $em;
        $this->commandHandler = $commandHandler;
        $this->mailer         = $mailer;
        $this->senderMail     = $senderMail;
        $this->receiverMail   = $receiverMail;
    }

    /**
     * Configures the command, sets helptext and parameters.
     */
    protected function configure(): void
    {
        $this->setName('app:zulu:devices:turnoff')->setDescription('Turns all devices off.');
    }

    /**
     * This is the entry-point when running the command from the CLI.
     *
     * @return int|null
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $text = '';
        $io   = new SymfonyStyle($input, $output);
        $io->title('Geräte ausschalten');
        /* @var Zulu[] $zulus */
        $zulus = $this->em->getRepository(Zulu::class)->findAll();
        /** @var AbstractCommand $command */
        $command        = $this->em->getRepository(ZuluCommand::class)->findOneBy(['name' => 'cmd_shutdownAll']);
        foreach ($zulus as $zulu) {
            $this->commandHandler->setZulu($zulu);
            $io->text('Aktueller Status:');
            $status = $this->commandHandler->getStatusOfZulu();
            $io->text(json_encode($status));
            $text .= sprintf("Raum %s\n", $zulu->getRoom());
            // Add this, when an intelligent method of implementing this function has been accepted. CommandsHandler::statusToHumanReadable($status);
            try {
                if (true !== $status['12']) {
                    $io->text(sprintf('Geräte im Raum "%s" abstellen.', $zulu->getRoom()));
                    $this->commandHandler->runCommand($command);
                }
            } catch (Exception $e) {
                $io->text(sprintf('Exception: "%s"', $e->getMessage()));
                $io->text(sprintf('Geräte im Raum "%s" abstellen.', $zulu->getRoom()));
                $this->commandHandler->runCommand($command);
            }
        }

        $message = new Swift_Message();
        $message
            ->setSubject('Cron message')
            ->setFrom($this->senderMail)
            ->setTo($this->receiverMail)
            ->setBody($text);
        $this->mailer->send($message);

        return 0;
    }
}
