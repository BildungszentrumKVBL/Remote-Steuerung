<?php

namespace App\Entity;

use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Group.
 *
 * This class represents the groups that the users can have.
 *
 * @ORM\Table(name="app_user_group")
 * @ORM\Entity()
 */
class Group extends BaseGroup
{
    /**
     * This is the id that will be placed in the database after the persisting of this object.
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int $id
     */
    protected $id;

    /**
     * The name of the group.
     *
     * @var string $name
     */
    protected $name;

    /**
     * The roles that are associated to this group.
     *
     * @var array $roles
     */
    protected $roles;

    /**
     * Group constructor.
     *
     * @param string $name
     * @param array  $roles
     */
    public function __construct(string $name, array $roles = [])
    {
        parent::__construct($name);
        $this->setRoles($roles);
    }
}
