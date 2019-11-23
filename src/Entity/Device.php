<?php

namespace App\Entity;

use ReflectionClass;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Device.
 *
 * @ORM\Entity
 * @ORM\Table(name="device")
 * @UniqueEntity("messagingId")
 */
class Device
{
    const TYPE_ANDROID = 'android';

    const TYPE_IOS = 'ios';

    const TYPE_WEB = 'web';

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int $id
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string $name
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="devices")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     * @var User $user
     */
    private $user;

    /**
     * @ORM\Column(type="string")
     *
     * @var string $type
     */
    private $type;

    /**
     * @ORM\Column(type="string")
     *
     * @var string $messagingId
     */
    private $messagingId;

    /**
     * Device constructor.
     *
     * @param string $name
     * @param string $messagingId
     * @param string $type
     */
    public function __construct(string $name, string $messagingId, string $type)
    {
        $this->name        = $name;
        $this->messagingId = $messagingId;
        $this->setType($type);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $rc        = new ReflectionClass(__CLASS__);
        $constants = $rc->getConstants();
        foreach ($constants as $name => $value) {
            if (substr($name, 0, 5) === 'TYPE_') {
                $this->type = $type;
            }
        }
    }

    /**
     * @return string
     */
    public function getMessagingId(): string
    {
        return $this->messagingId;
    }

    /**
     * @param string $messagingId
     */
    public function setMessagingId(string $messagingId)
    {
        $this->messagingId = $messagingId;
    }
}
