<?php

namespace AppBundle\Service;

use AppBundle\Entity\Log;
use AppBundle\Entity\Timegrid;
use Doctrine\ORM\EntityManager;

/**
 * Class WebUntisHandler.
 *
 * This service handles the WebUntis-API.
 */
class WebUntisHandler
{
    /**
     * The URL of the WebUntis-API
     */
    const API = 'https://erato.webuntis.com/WebUntis/jsonrpc.do';

    /**
     * The name of the school.
     *
     * @var string $school
     */
    private $school;

    /**
     * The username for the API.
     *
     * @var string $username
     */
    private $username;

    /**
     * The API-client. Name of the application.
     *
     * @var string $apiClient
     */
    private $apiClient;

    /**
     * The password for the API.
     *
     * @var string $password
     */
    private $password;

    /**
     * The session id from WebUntis.
     *
     * @var int $session
     */
    private $session;

    /**
     * The EntityManager for database-interactions.
     *
     * @var EntityManager $em
     */
    private $em;

    /**
     * WebUntisHandler constructor.
     *
     * @param EntityManager $em
     * @param string        $school
     * @param string        $username
     * @param string        $password
     * @param string        $apiClient
     */
    public function __construct(
        EntityManager $em,
        string $school,
        string $username,
        string $password,
        string $apiClient
    ) {
        $this->em        = $em;
        $this->school    = $school;
        $this->username  = $username;
        $this->password  = $password;
        $this->apiClient = $apiClient;
    }

    /**
     * Gets session from WebUntis.
     */
    public function login()
    {
        $login  = [
            'id'      => 4328942342,
            'method'  => 'authenticate',
            'params'  => [
                'user'     => $this->username,
                'password' => $this->password,
                'client'   => $this->apiClient,
            ],
            'jsonrpc' => '2.0',
        ];
        $curl   = $this->getBaseCurl($login);
        $result = curl_exec($curl);

        $response = json_decode($result);
        try {
            if (!property_exists($response, 'error')) {
                if ($response->result->sessionId) {
                    $this->session = $response->result->sessionId;
                }
            }
        } catch (\Exception $e) {
            $log = new Log('Login von WebUntisHandler ist fehlgeschlagen.', Log::LEVEL_ERROR);
            $this->em->persist($log);
            $this->em->flush();
        }

        return $this;
    }

    /**
     * Returns the base curl for the service.
     *
     * @param array $data
     *
     * @return resource
     */
    private function getBaseCurl(array $data)
    {
        $data = json_encode($data);
        $url  = (empty($this->session)) ? self::API."?school=".$this->school : self::API.';jsessionid='.$this->session;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 1500);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt(
            $curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: '.strlen($data),
            )
        );

        return $curl;
    }

    /**
     * Returns the room that the teacher reserved.
     *
     * @param string $username
     *
     * @return null
     */
    public function getRoomForTeacher(string $username)
    {
        $teacher = $this->getTeacher($username);
        if (!$teacher) {
            return null;
        }
        $timetable = $this->getTimetableForTeacher($teacher->id);
        $datetime  = (int) str_replace(':', '', date('H:i', strtotime('-5 minutes')));
        $roomId    = null;
        foreach ($timetable as $time) {
            if ($time->startTime <= $datetime && $time->endTime >= $datetime) {
                $roomId = $time->ro[0]->id;
            }
        }
        if ($roomId) {
            $rooms   = $this->getRooms();
            $roomname = null;
            foreach ($rooms as $room) {
                if ($room->id === $roomId) {
                    $roomname = $room->name;
                }
            }

            return $roomname;
        } else {
            return null;
        }
    }

    /**
     * Gets the teacher-object from WebUntis.
     *
     * @param string $username
     *
     * @return null|array
     */
    public function getTeacher(string $username)
    {
        $teachers = $this->getTeachers();
        if (!$teachers) {
            return null;
        }
        $teacher = array_filter(
            $teachers, function ($value) use ($username) {
            return ($value->name === $username && $value->active);
        }
        );
        if (count($teacher) === 1) {
            return array_values($teacher)[0];
        } else {
            return null;
        }
    }

    /**
     * Get all teacher-objects from WebUntis.
     *
     * @return null|array
     */
    public function getTeachers()
    {
        $request  = [
            'id'      => 4328942341,
            'method'  => 'getTeachers',
            'params'  => [],
            'jsonrpc' => '2.0',
        ];
        $curl     = $this->getBaseCurl($request);
        $result   = curl_exec($curl);
        $response = json_decode($result);
        try {
            if (!property_exists($response, 'error')) {
                return $response->result;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            $log = new Log('getTeachers Funktion von WebUntisHandler ist fehlgeschlagen.', Log::LEVEL_ERROR);
            $this->em->persist($log);
            $this->em->flush();
        }
    }

    /**
     * Gets the Timetable for the id of the teacher-object.
     *
     * @param int $id
     *
     * @return null|array
     */
    public function getTimetableForTeacher(int $id)
    {
        $request  = [
            'id'      => 4328942342,
            'method'  => 'getTimetable',
            'params'  => [
                'id'   => $id,
                'type' => 2,
            ],
            'jsonrpc' => '2.0',
        ];
        $curl     = $this->getBaseCurl($request);
        $result   = curl_exec($curl);
        $response = json_decode($result);
        try {
            if (!property_exists($response, 'error')) {
                return $response->result;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            $log = new Log('getTimetableForTeacher Funktion von WebUntisHandler ist fehlgeschlagen.', Log::LEVEL_ERROR);
            $this->em->persist($log);
            $this->em->flush();
        }
    }

    /**
     * Gets the rooms that are in WebUntis.
     *
     * @return null
     */
    private function getRooms()
    {
        $request  = [
            'id'      => 4328942321,
            'method'  => 'getRooms',
            'params'  => [],
            'jsonrpc' => '2.0',
        ];
        $curl     = $this->getBaseCurl($request);
        $result   = curl_exec($curl);
        $response = json_decode($result);
        try {
            if (!property_exists($response, 'error')) {
                return $response->result;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            $log = new Log('getRooms Funktion von WebUntisHandler ist fehlgeschlagen.', Log::LEVEL_ERROR);
            $this->em->persist($log);
            $this->em->flush();
        }
    }

    /**
     * Updates the  Timegrid.
     */
    public function updateTimegrid()
    {
        $schedule = $this->getTodaySchedule();
        if (!$schedule) {
            $log = new Log('Timegrid konnte nicht aktualisiert werden.', Log::LEVEL_ERROR);
            $this->em->persist($log);
            $this->em->flush();

            return;
        }

        $dbTimegrids = $this->em->getRepository('AppBundle:Timegrid')->findAll();
        foreach ($dbTimegrids as $timegrid) {
            $this->em->remove($timegrid);
        }
        $this->em->flush();

        foreach ($schedule as $sch) {
            $timegrid = new Timegrid(TimeGrid::intToDateTime($sch->startTime), Timegrid::intToDateTime($sch->endTime));
            $this->em->persist($timegrid);
        }
        $this->em->flush();
    }

    /**
     * Returns the schedule for today.
     *
     * @return mixed
     */
    public function getTodaySchedule()
    {
        $datetime  = new \DateTime();
        $weekday   = (int) $datetime->format('w') + 1;
        $timeUnits = null;
        $schedule  = array_filter(
            $this->getSchedules(), function ($value) use ($weekday, &$timeUnits) {
            return ($value->day === $weekday);
        }
        );

        if (count($schedule) === 1) {
            return array_values($schedule)[0]->timeUnits;
        } else {
            return null;
        }
    }

    /**
     * Get schedules.
     *
     * @return null|array
     */
    private function getSchedules()
    {
        $request = [
            'id'      => 4328942331,
            'method'  => 'getTimegridUnits',
            'params'  => [],
            'jsonrpc' => '2.0',
        ];
        $curl    = $this->getBaseCurl($request);
        $result  = curl_exec($curl);

        $response = json_decode($result);
        try {
            if (!property_exists($response, 'error')) {
                return $response->result;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            $log = new Log('getSchedules Funktion von WebUntisHandler ist fehlgeschlagen.', Log::LEVEL_ERROR);
            $this->em->persist($log);
            $this->em->flush();
        }
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Cleans session.
     */
    public function onKernelResponse()
    {
        if ($this->session) {
            $this->logout();
            $this->session = null;
        }
    }

    /**
     * Logs out from WebUntis.
     */
    private function logout()
    {
        $logout = [
            'id'      => 4328942343,
            'method'  => 'logout',
            'params'  => [],
            'jsonrpc' => '2.0',
        ];

        $curl = $this->getBaseCurl($logout);
        curl_exec($curl);
    }

}
