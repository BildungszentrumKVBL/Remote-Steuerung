<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Entity\View;
use Doctrine\ORM\EntityManager;

/**
 * Class TwigHelper.
 */
class TwigHelper
{
    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * TwigHelper constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return \AppBundle\Entity\View[]|array
     */
    public function getViews()
    {
        return $this->em->getRepository('AppBundle:View')->findAll();
    }

    /**
     * @param View $view
     *
     * @return View|mixed
     */
    public function getNextView(View $view)
    {
        $views = $this->getViews();

        // Array pointer to the last element.
        end($views);

        if (current($views) === $view) {
            reset($views);

            return current($views);
        }

        reset($views);

        if (in_array($view, $views)) {
            while (current($views) !== $view) {
                next($views);
            }

            return next($views);
        }

        return $view;
    }

    /**
     * @param View $view
     *
     * @return View|mixed
     */
    public function getPreviousView(View $view)
    {
        $views = $this->getViews();

        if (current($views) === $view) {
            return end($views);
        }

        if (in_array($view, $views)) {
            while (current($views) !== $view) {
                next($views);
            }

            return prev($views);
        }

        return $view;
    }

    /**
     * @param User $user
     *
     * @return \AppBundle\Entity\Room|null|object
     */
    public function getRoomForUser(User $user)
    {
        $zulu = $this->em->getRepository('AppBundle:Zulu')->findOneBy(['lockedBy' => $user->getUsername()]);

        return ($zulu) ? $zulu->getRoom() : null;
    }
}
