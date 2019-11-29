<?php

namespace App\Command;

use App\Entity\Room;
use App\Entity\Zulu;
use App\Service\CommandsHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GetStatusCommand.
 *
 * This command gets the status from a zulu in a specific room. It is primarily used to create sub-processes when
 * fetching a large amount statuses from zulu.
 */
class GetStatusCommand extends Command
{
    private $em;
    private $commandHandler;

    /**
     * GetStatusCommand constructor.
     */
    public function __construct(EntityManagerInterface $em, CommandsHandler $commandHandler)
    {
        parent::__construct(null);

        $this->em             = $em;
        $this->commandHandler = $commandHandler;
    }

    /**
     * Configures the command, sets helptext and parameters.
     */
    protected function configure(): void
    {
        $this->setName('app:status:get')->setDescription('Gibt Status der Zulu aus.');
        $this->addArgument('room', InputArgument::REQUIRED, 'Zulu, dessen Status du wissen willst.');
    }

    /**
     * This is the entry-point when running the command from the CLI.
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $room = $this->em->getRepository(Room::class)->findOneBy(['name' => $input->getArgument('room')]);
        /* @var Zulu $zulu */
        $zulu = $this->em->getRepository(Zulu::class)->findOneBy(['room' => $room]);
        if (!$zulu) {
            $output->write(json_encode(null));

            return 0;
        }
        $this->commandHandler->setZulu($zulu);

        $output->write(json_encode($this->commandHandler->getStatusOfZulu()));

        return 0;
    }
}
