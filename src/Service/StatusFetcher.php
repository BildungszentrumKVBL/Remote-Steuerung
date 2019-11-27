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
    /**
     * The EntityManager for database-interactions.
     *
     * @var EntityManager
     */
    private $em;

    /**
     * The root-dirctory of the applcation.
     *
     * @var string
     */
    private $rootDir;

    /**
     * StatusFetcher constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManagerInterface $em, string $projectDir)
    {
        $this->em      = $em;
        $this->rootDir = $projectDir;
    }

    /**
     * Fetches the status of a or multiple Zulus through the CLI.
     *
     * @param null $zulus
     */
    public function fetch($zulus = null): array
    {
        if (null === $zulus) {
            $zulus = $this->em->getRepository(Zulu::class)->findAll();
        }
        $statuses  = [];
        $processes = [];
        foreach ($zulus as $zulu) {
            $process     = new Process('php bin/console app:status:get '.$zulu->getRoom(), $this->rootDir);
            $processes[] = $process;
        }

        foreach ($processes as $process) {
            /* @var Process $process */
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
