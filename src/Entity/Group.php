<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\Group as BaseGroup;

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
     * @var int
     */
    protected $id;

    /**
     * The name of the group.
     *
     * @var string
     */
    protected $name;

    /**
     * The roles that are associated to this group.
     *
     * @var array
     */
    protected $roles;

    /**
     * Group constructor.
     */
    public function __construct(string $name, array $roles = [])
    {
        parent::__construct($name);
        $this->setRoles($roles);
    }
}
