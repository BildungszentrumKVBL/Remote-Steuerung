<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class UserRepository.
 *
 * This repository acts as an extension to the `User`-entity.
 * It will be obsolete in a future version.
 */
class UserRepository extends EntityRepository
{
    /**
     * TODO: Refactor, make lockedBy unique.
     *
     * Runs a custom DQL-query that gets the locked zulu of the user.
     *
     * @see [The Doctrine-Query-Language](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html)
     *
     * @return Zulu
     */
    public function getLockedZulu(string $username): ?Zulu
    {
        $zulus = $this->getEntityManager()->getRepository(Zulu::class)->findBy(['lockedBy' => $username]);
        $zulu  = (0 !== count($zulus)) ? $zulus[0] : null;

        return $zulu;
    }
}
