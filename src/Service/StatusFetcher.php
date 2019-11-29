<?php

namespace App\Service;

use App\Entity\Zulu;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Process\Process;

/**
 * Class StatusFetcher.
 *
 * This service helps getting the status of multiple Zulus.
 */
class StatusFetcher
{
    private $em;

    private $projectDir;

    /**
     * StatusFetcher constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManagerInterface $em, string $projectDir)
    {
        $this->em         = $em;
        $this->projectDir = $projectDir;
    }

    /**
     * Fetches the status of a or multiple Zulus through the CLI.
     *
     * @param Zulu[]|null $zulus
     */
    public function fetch(array $zulus = null): array
    {
        if (null === $zulus) {
            $zulus = $this->em->getRepository(Zulu::class)->findAll();
        }
        $statuses = [];
        /* @var Process[] $processes */
        $processes = [];
        foreach ($zulus as $zulu) {
            $process     = new Process(['php', 'bin/console', 'app:status:get', $zulu->getRoom()], $this->projectDir);
            $processes[] = $process;
        }

        foreach ($processes as $process) {
            $process->start();
        }

        while ($this->fetchLoop($processes, $statuses)) {
            // All processes are finished.
        }

        return $statuses;
    }

    /**
     * This function contains a loop that helps with multi-threading.
     *
     * It waits until all processes are done.
     *
     * @param $processes
     * @param $statuses
     *
     * @return bool
     */
    private function fetchLoop(&$processes, &$statuses): bool
    {
        foreach ($processes as $key => $process) {
            /* @var Process $process */
            if ($process->isRunning()) {
                return true;
            } else {
                $statuses[$key] = json_decode($process->getOutput());
                unset($processes[$key]);
            }
        }

        return false;
    }
}
