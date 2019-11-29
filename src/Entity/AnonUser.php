<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * Class AnonUser.
 *
 * This class is currently not in use. It will be used when **QR-Codes**, **NFC-Tags** and **Eddystone-URL** devices
 * are ready and the backend supports it.
 */
class AnonUser extends BaseUser
{
    /**
     * This is the id that will be placed in the database after the persisting of this object.
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * The displayed username this anonym user should get.
     *
     * @ORM\Column(type="string", length=25)
     *
     * @var string
     */
    protected $username;

    /**
     * This is the generated token that will be used to authenticate the user via the url.
     *
     * @ORM\Column(type="string", length=32)
     *
     * @var string
     */
    protected $token;

    /**
     * AnonUser constructor.
     */
    public function __construct(string $username, string $token)
    {
        parent::__construct();
        $this->username = $username;
        $this->token    = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }
}
