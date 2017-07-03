<?php

namespace AppBundle\Command;

use AppBundle\Entity\Timegrid;
use AppBundle\Entity\Zulu;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UnlockZuluCommand.
 *
 * This command unlocks all zulus at the ending of the lesson. This will prevent teachers from locking the rooms when
 * other teachers want to use this application. This command should run at 1-2 minutes.
 */
class UnlockZuluCommand extends ContainerAwareCommand
{
    /**
     * Configures the command, sets helptext and parameters.
     */
    protected function configure()
    {
        $this->setName('app:zulu:unlock_free')->setDescription('Unlocks zulus which are since 10min not in use in WebUntis.');
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
        $io        = new SymfonyStyle($input, $output);
        $datetime  = date('H:i', strtotime('-10 min'));
        /** @var Timegrid[] $timegrids */
        $timegrids = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('AppBundle:Timegrid')->findAll();
        foreach ($timegrids as $timegrid) {
            if ($timegrid->getEnd()->format('H:i') === $datetime) {
                $io->title('Zulu unlocken');
                $io->section('Zulus');
                $this->freeZulus($io);
            }
        }

        return 0;
    }

    /**
     * Unlock Zulu when not reserved WebUntis and 10min after the end of a lesson.
     *
     * @param SymfonyStyle $io
     */
    protected function freeZulus(SymfonyStyle $io)
    {
        $em    = $this->getContainer()->get('doctrine.orm.entity_manager');
        $zulus = $em->createQueryBuilder()->select('z')->from('AppBundle:Zulu', 'z')->where('z.locked = true')->getQuery()->getResult();
        $wh    = $this->getContainer()->get('app.webuntis.handler')->login();
        foreach ($zulus as $zulu) {
            /* @var Zulu $zulu */
            $room = $wh->getRoomForTeacher($zulu->getLockedBy());
            if ($zulu->getRoom() !== $room) {
                $zulu->unlock();
                $io->text(sprintf('Unlock %s', $zulu->getRoom()));
                $em->persist($zulu);
            }
        }
        $em->flush();
    }
}
