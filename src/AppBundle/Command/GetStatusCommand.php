<?php

namespace AppBundle\Command;

use AppBundle\Entity\Room;
use AppBundle\Entity\Zulu;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GetStatusCommand.
 *
 * This command gets the status from a zulu in a specific room. It is primarily used to create sub-processes when
 * fetching a large amount statuses from zulu.
 */
class GetStatusCommand extends ContainerAwareCommand
{
    /**
     * Configures the command, sets helptext and parameters.
     */
    protected function configure()
    {
        $this->setName('app:status:get')->setDescription('Gibt Status der Zulu aus.');
        $this->addArgument('room', InputArgument::REQUIRED, 'Zulu, dessen Status du wissen willst.');
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
        $room = $em->getRepository(Room::class)->findOneBy(['name' => $input->getArgument('room')]);
        /** @var Zulu $zulu */
        $zulu = $em->getRepository(Zulu::class)->findOneBy(['room' => $room]);
        if (!$zulu) {
            $output->write(json_encode(null));

            return 0;
        }
        $commandHandler = $this->getContainer()->get('command_handler');
        $commandHandler->setZulu($zulu);

        $output->write(json_encode($commandHandler->getStatusOfZulu()));

        return 0;
    }
}
