<?php

namespace AppBundle\Entity;

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
     * Runs a custom DQL-query that gets the locked zulu of the user.
     *
     * @see [The Doctrine-Query-Language](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html)
     *
     * @param string $username
     *
     * @return Zulu
     */
    public function getLockedZulu(string $username)
    {
        $zulus = $this->getEntityManager()->createQuery('SELECT z FROM AppBundle:Zulu z WHERE z.lockedBy = :username')->setParameter(':username', $username)->getResult();
        $zulu  = (count($zulus) !== 0) ? $zulus[0] : null;

        return $zulu;
    }
}
