<?php

namespace App\Service;

use App\Entity\AbstractCommand;
use App\Entity\EventGhostCommand;
use App\Entity\Log;
use App\Entity\User;
use App\Entity\Zulu;
use App\Entity\ZuluCommand;
use App\Entity\ZuluCommandStatus;
use App\Entity\ZuluStatus;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use SimpleXMLElement;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CommandsHandler.
 *
 * This service handles all commands for the application.
 */
class CommandsHandler
{
    /**
     * The User of the current request.
     *
     * @var User
     */
    private $user;

    /**
     * The EntityManager that handle database-interactions.
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * The Zulu that is the target for `ZuluCommand`s.
     *
     * @var Zulu
     */
    private $zulu;

    /**
     * The validator to validate EventGhostCommands.
     *
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * The port that EventGhost listens on.
     *
     * @var string
     */
    private $egPort;

    /**
     * The username for the authentication for EventGhost.
     *
     * @var string
     */
    private $egUsername;

    /**
     * The password for the authentication for EventGhost.
     *
     * @var string
     */
    private $egPassword;

    /**
     * CommandsHandler constructor.
     */
    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $storage,
        ValidatorInterface $validator,
        string $egPort,
        string $egUsername,
        string $egPassword
    ) {
        $this->em = $em;
        $token    = $storage->getToken();
        if (null !== $token) {
            $this->user = $token->getUser();
            $this->zulu = $this->em->getRepository(User::class)->getLockedZulu($this->user->getUsername());
        }

        $this->validator  = $validator;
        $this->egPort     = $egPort;
        $this->egUsername = $egUsername;
        $this->egPassword = $egPassword;
    }

    /**
     * Runs the command that is passed.
     *
     * @return string
     *
     * @throws \Exception
     */
    public function runCommand(AbstractCommand $command)
    {
        if ($command instanceof ZuluCommand) {
            $ip  = $this->zulu->getIp();
            $uri = $command->getUri();
            $xml = $this->doRequest($ip.$uri);
            if (false === $xml) {
                return $xml;
            }
            // Request was successful.
            $values = new SimpleXMLElement($xml);
            sleep((int) $values->KeyLockTime[0]);

            return $xml;
        } elseif ($command instanceof EventGhostCommand) {
            $constaints = $this->validator->validate($command);
            if ($constaints->count() > 0) {
                throw new Exception('Command ist nicht valide.');
            }
            $ip  = $this->zulu->getRoom()->getComputer()->getName();
            $uri = $command->getUri();
            $this->doRequest(sprintf('%s:%s@%s:%s%s', $this->egUsername, $this->egPassword, $ip, $this->egPort, $uri));
        } else {
            throw new Exception('Nicht existierender Command wurde getätigt.');
        }

        return null;
    }

    /**
     * Runs a request and returns the output.
     *
     * @return string
     */
    private function doRequest(string $url)
    {
        $curl = curl_init($url);
        $this->modifyCurl($curl);
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }

    /**
     * Modifies the curl object by reference.
     *
     * @param resource $curl
     */
    private function modifyCurl(&$curl)
    {
        curl_setopt($curl, CURLOPT_POST, 0);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 500);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * Gets the status of the Zulu that is passed.
     *
     * @return mixed
     */
    public function getStatusOfZulu()
    {
        $dt   = microtime(true);
        $curl = curl_init($this->zulu->getStatusUrl());
        $this->modifyCurl($curl);
        $output  = curl_exec($curl);
        $dt2     = microtime(true);
        $latency = round($dt2 - $dt, 3) * 1000;
        $log     = new Log(sprintf('Latenz: %sms für die Zulu im Zimmer %s', $latency, $this->zulu->getRoom()), Log::LEVEL_INFO, $this->user);
        $this->em->persist($log);

        if ($output) { // Request was successful
            $commandStatuses = $this->statusXmlToZuluCommandStatuses($output);
            $status          = new ZuluStatus();
            foreach ($commandStatuses as $commandStatus) {
                $status->addCommandStatus($commandStatus);
            }
            $this->zulu->addStatus($status);
            $this->em->persist($this->zulu);
            $this->em->flush($this->zulu);
        }

        return $this->convertToStatus($output);
    }

    /**
     * Converts ugly-ass api of zulu and converts it into a list of ZuluCommandStatus objects.
     */
    private function statusXmlToZuluCommandStatuses(string $xml): array
    {
        $object          = simplexml_load_string($xml);
        $raw_array       = json_decode(json_encode($object), true)['Elements'];
        $commandStatuses = [];

        foreach ($raw_array as $name => $data) {
            if ('ChangeId' === $name) { // The API sends a ChangeId withing the same scope as the data...
                continue;
            }
            $id = (int) str_replace('Id', '', $name); // We get the id as a key named 'Id1' or 'Id3'...
            // We get the actual status as a property of the $data variable... Its called 'Image' and holds a 1 (Off) or 2 (On).
            $command = $this->em->getRepository(ZuluCommand::class)->findOneBy(['commandId' => $id]);
            if ($command) {
                $commandStatus     = new ZuluCommandStatus($command, (bool) ($data['Image'] - 1));
                $commandStatuses[] = $commandStatus;
            }
        }

        return $commandStatuses;
    }

    /**
     * Converts output into a status.
     *
     * @param $output
     *
     * @return array|bool
     */
    private function convertToStatus($output)
    {
        if (false === $output) {
            return $output;
        }

        $values = new SimpleXMLElement($output);
        $status = [];
        foreach ($values as $value) {
            foreach ($value as $name => $val) {
                if ('Id' === substr($name, 0, 2)) {
                    $id          = (int) str_replace('Id', '', $name);
                    $status[$id] = ('2' == $val[0]->Image);
                }
            }
        }

        return $status;
    }

    /**
     * Sets the Zulu when the target Zulu is not the Zulu of the user of the current session.
     *
     * @return $this
     */
    public function setZulu(Zulu $zulu)
    {
        $this->zulu = $zulu;

        return $this;
    }
}
