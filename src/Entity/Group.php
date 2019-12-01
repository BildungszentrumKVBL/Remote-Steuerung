<?php

namespace App\Entity;

use App\Entity\Traits\Id;
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
    use Id;

    /**
     * TODO: Has to be added explicitly. Else there is a MappingException probably because of FosUser.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
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
