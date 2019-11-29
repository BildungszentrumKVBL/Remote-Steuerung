<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ReflectionClass;
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
    public const TYPE_ANDROID = 'android';

    public const TYPE_IOS = 'ios';

    public const TYPE_WEB = 'web';

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="devices")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $messagingId;

    /**
     * Device constructor.
     */
    public function __construct(string $name, string $messagingId, string $type)
    {
        $this->name        = $name;
        $this->messagingId = $messagingId;
        $this->setType($type);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $rc        = new ReflectionClass(__CLASS__);
        $constants = $rc->getConstants();
        foreach ($constants as $name => $value) {
            if ('TYPE_' === substr($name, 0, 5)) {
                $this->type = $type;
            }
        }
    }

    public function getMessagingId(): string
    {
        return $this->messagingId;
    }

    public function setMessagingId(string $messagingId): void
    {
        $this->messagingId = $messagingId;
    }
}
