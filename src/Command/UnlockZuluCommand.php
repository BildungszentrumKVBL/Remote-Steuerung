<?php

namespace App\Command;

use App\Entity\Timegrid;
use App\Entity\Zulu;
use App\Service\WebUntisHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UnlockZuluCommand.
 *
 * This command unlocks all zulus at the ending of the lesson. This will prevent teachers from locking the rooms when
 * other teachers want to use this application. This command should run at 1-2 minutes.
 */
class UnlockZuluCommand extends Command
{
    private $em;
    private $webUntisHandler;

    /**
     * UnlockZuluCommand constructor.
     */
    public function __construct(EntityManagerInterface $em, WebUntisHandler $webUntisHandler)
    {
        parent::__construct(null);

        $this->em              = $em;
        $this->webUntisHandler = $webUntisHandler;
    }

    protected function configure(): void
    {
        $this->setName('app:zulu:unlock_free')->setDescription('Unlocks zulus which are since 10min not in use in WebUntis.');
    }

    /**
     * This is the entry-point when running the command from the CLI.
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io        = new SymfonyStyle($input, $output);
        $datetime  = date('H:i', strtotime('-10 min'));
        /** @var Timegrid[] $timegrids */
        $timegrids = $this->em->getRepository(Timegrid::class)->findAll();
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
     */
    protected function freeZulus(SymfonyStyle $io): void
    {
        $zulus = $this->em->getRepository(Zulu::class)->findBy(['locked' => true]);
        $wh    = $this->webUntisHandler->login();
        foreach ($zulus as $zulu) {
            /* @var Zulu $zulu */
            $room = $wh->getRoomForTeacher($zulu->getLockedBy());
            if ($zulu->getRoom() !== $room) {
                $zulu->unlock();
                $io->text(sprintf('Unlock %s', $zulu->getRoom()));
                $this->em->persist($zulu);
            }
        }
        $this->em->flush();
    }
}
