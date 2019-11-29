<?php

namespace App\DataFixtures;

use App\Entity\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadGroupData.
 *
 * This class contains the current 4 groups. This will be abstracted into a YAML file, just like the other fixtures.
 * This will allow the user to easier modify the base data.
 * The groups in this fixture are:
 *  - `Teacher`: Contains the `ROLE_TEACHER`
 *  - `IT-Teacher`: Contains the `ROLE_IT_TEACHER`
 *  - `IT`: Contains the `ROLE_IT`
 *  - `HW`: Contains the `ROLE_HW`
 */
class LoadGroupData extends Fixture
{
    /**
     * Load data fixtures with the passed ObjectManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $group  = new Group('Teacher', ['ROLE_TEACHER']);
        $group1 = new Group('IT-Teacher', ['ROLE_IT_TEACHER']);
        $group2 = new Group('IT', ['ROLE_IT']);
        $group3 = new Group('HW', ['ROLE_HW']);

        $manager->persist($group);
        $manager->persist($group1);
        $manager->persist($group2);
        $manager->persist($group3);

        $manager->flush();
    }
}
