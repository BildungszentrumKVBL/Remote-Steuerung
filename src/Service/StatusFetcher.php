<?php

namespace App\Service;

use App\Entity\Zulu;
use Doctrine\ORM\EntityManager;
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
     * @var EntityManager $em
     */
    private $em;


    /**
     * The root-dirctory of the applcation.
     *
     * @var string $rootDir
     */
    private $rootDir;

    /**
     * StatusFetcher constructor.
     *
     * @param EntityManager   $em
     * @param string          $kernelRootDir
     */
    public function __construct(EntityManager $em, string $kernelRootDir)
    {
        $this->em             = $em;
        $this->rootDir        = $kernelRootDir.'/../';
    }

    /**
     * Fetches the status of a or multiple Zulus through the CLI.
     *
     * @param null $zulus
     *
     * @return array
     */
    public function fetch($zulus = null): array
    {
        if ($zulus === null) {
            $zulus = $this->em->getRepository(Zulu::class)->findAll();
        }
        $statuses  = [];
        $processes = [];
        foreach ($zulus as $zulu) {
            $process     = new Process('php app/console app:status:get '.$zulu->getRoom(), $this->rootDir);
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
