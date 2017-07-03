<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class User.
 *
 * This entity represents the user. IT, HW and teachers.
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
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
     * @var int $id
     */
    protected $id;

    /**
     * The distinguished name that is stored in the AD.
     *
     * @see [Active Directory](https://msdn.microsoft.com/en-us/library/bb742424.aspx)
     *
     * @ORM\Column(name="distinguished_name", type="string", nullable=true)
     *
     * @var string $dn
     */
    protected $dn;

    /**
     * This property temporarily hold the LDAP-groups that are associated with the user on his first login.
     *
     * @var array $ldapGroups
     */
    protected $ldapGroups = array();

    /**
     * These are the groups that this user is in.
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Group")
     * @ORM\JoinTable(name="app_user_has_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     *
     * @var array $groups
     */
    protected $groups = array();

    /**
     * The firstname of the user. Which is stored in the AD.
     *
     * @see [Active Directory](https://msdn.microsoft.com/en-us/library/bb742424.aspx)
     *
     * @ORM\Column(name="first_name", nullable=true)
     *
     * @var string $firstName
     */
    private $firstName;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Device", mappedBy="user", cascade={"persist", "remove"})
     *
     * @var Device|ArrayCollection $devices
     */
    private $devices;

    /**
     * The lastname of the user. Which is stored in the see.
     *
     * @ORM\Column(name="last_name", nullable=true)
     *
     * @var string $lastName
     */
    private $lastName;

    /**
     * The settings which are specific to the user.
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\UserSettings", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="settings_id", referencedColumnName="id")
     *
     * @var UserSettings $settings
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
     *
     * @param string $username
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     *
     * @return User
     */
    static function createFromProperties(string $username, string $email, string $firstName, string $lastName): User
    {
        $self            = new self();
        $self->username  = $username;
        $self->email     = $email;
        $self->firstName = $firstName;
        $self->lastName  = $lastName;

        return $self;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Needed from ldap-config.
     *
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Needed from ldap-config.
     *
     * @param string $lastName
     *
     * @return $this
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDn()
    {
        return $this->dn;
    }

    /**
     * @param string $dn
     *
     * @return $this
     */
    public function setDn($dn)
    {
        $this->dn = $dn;

        return $this;
    }

    /**
     * Add LDAP-groups to the user.
     *
     * @param array $ldapGroups
     *
     * @return $this
     */
    public function setLdapGroups(array $ldapGroups)
    {
        $groups = array();
        foreach ($ldapGroups as $group) {
            $r = preg_match('/CN=([\w\s-äÄöÖüÜß]*),/', $group, $results);
            if ($r === 1) {
                array_push($groups, $results[1]);
            }
        }
        $this->ldapGroups = $groups;

        return $this;
    }

    /**
     * @return array
     */
    public function getLdapGroups(): array
    {
        return $this->ldapGroups;
    }

    /**
     * @return bool return true if this user is an ldap user.
     */
    public function isLdapUser(): bool
    {
        return (bool) $this->dn;
    }

    /**
     * @return UserSettings
     */
    public function getSettings(): UserSettings
    {
        return $this->settings;
    }

    /**
     * @param UserSettings $settings
     */
    public function setSettings(UserSettings $settings)
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

    /**
     * @param Device $device
     */
    public function addDevice(Device $device)
    {
        if (!$this->devices->contains($device)) {
            $device->setUser($this);
            $this->devices->add($device);
        }
    }

    /**
     * @param Device $device
     */
    public function removeDevice(Device $device)
    {
        $this->devices->removeElement($device);
    }
}
