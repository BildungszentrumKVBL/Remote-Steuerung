<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class User.
 *
 * This entity represents the user. IT, HW and teachers.
 *
 * @ORM\Entity(repositoryClass="App\Entity\UserRepository")
 * @ORM\Table(name="app_user")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 */
class User extends BaseUser implements LdapUserInterface
{
    /**
     * This is the id that will be placed in the database after the persisting of this object.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * The distinguished name that is stored in the AD.
     *
     * @see [Active Directory](https://msdn.microsoft.com/en-us/library/bb742424.aspx)
     *
     * @ORM\Column(name="distinguished_name", type="string", nullable=true)
     *
     * @var string
     */
    protected $dn;

    /**
     * This property temporarily hold the LDAP-groups that are associated with the user on his first login.
     *
     * @var array
     */
    protected $ldapGroups = [];

    /**
     * These are the groups that this user is in.
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Group")
     * @ORM\JoinTable(name="app_user_has_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     *
     * @var Collection
     */
    protected $groups;

    /**
     * The firstname of the user. Which is stored in the AD.
     *
     * @see [Active Directory](https://msdn.microsoft.com/en-us/library/bb742424.aspx)
     *
     * @ORM\Column(name="first_name", nullable=true)
     *
     * @var string
     */
    private $firstName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Device", mappedBy="user", cascade={"persist", "remove"})
     *
     * @var Device|ArrayCollection
     */
    private $devices;

    /**
     * The lastname of the user. Which is stored in the see.
     *
     * @ORM\Column(name="last_name", nullable=true)
     *
     * @var string
     */
    private $lastName;

    /**
     * The settings which are specific to the user.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\UserSettings", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="settings_id", referencedColumnName="id")
     *
     * @var UserSettings
     */
    private $settings;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->groups   = new ArrayCollection();
        $this->devices  = new ArrayCollection();
        $this->password = '';
        $this->settings = new UserSettings();
    }

    /**
     * Alternative constructor.
     */
    public static function createFromProperties(string $username, string $email, string $firstName, string $lastName): User
    {
        $self            = new self();
        $self->username  = $username;
        $self->email     = $email;
        $self->firstName = $firstName;
        $self->lastName  = $lastName;

        return $self;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Needed from ldap-config.
     *
     * @return $this
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Needed from ldap-config.
     *
     * @return $this
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getDn(): ?string
    {
        return $this->dn;
    }

    /**
     * @return $this
     */
    public function setDn(string $dn)
    {
        $this->dn = $dn;

        return $this;
    }

    /**
     * Add LDAP-groups to the user.
     *
     * @param string[] $ldapGroups
     *
     * @return $this
     */
    public function setLdapGroups(array $ldapGroups): void
    {
        $groups = [];
        foreach ($ldapGroups as $group) {
            $r = preg_match('/CN=([\w\s-äÄöÖüÜß]*),/', $group, $results);
            if (1 === $r) {
                array_push($groups, $results[1]);
            }
        }
        $this->ldapGroups = $groups;
    }

    public function getLdapGroups(): array
    {
        return $this->ldapGroups;
    }

    /**
     * @return bool return true if this user is an ldap user
     */
    public function isLdapUser(): bool
    {
        return (bool) $this->dn;
    }

    public function getSettings(): UserSettings
    {
        return $this->settings;
    }

    public function setSettings(UserSettings $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @return Device|ArrayCollection|Collection
     */
    public function getDevices(): Collection
    {
        return $this->devices;
    }

    public function addDevice(Device $device): void
    {
        if (!$this->devices->contains($device)) {
            $device->setUser($this);
            $this->devices->add($device);
        }
    }

    public function removeDevice(Device $device): void
    {
        $this->devices->removeElement($device);
    }
}
