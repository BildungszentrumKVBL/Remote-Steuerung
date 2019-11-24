<?php

namespace App\Twig;


use App\Entity\User;
use App\Entity\View;
use App\Entity\Zulu;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ViewsHelper extends AbstractExtension
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_views', [$this, 'getViews']),
            new TwigFunction('get_next_view', [$this, 'getNextView']),
            new TwigFunction('get_previous_view', [$this, 'getPreviousView']),
            new TwigFunction('get_room_for_user', [$this, 'getRoomForUser']),
        ];
    }

    /**
     * @return \App\Entity\View[]|array
     */
    public function getViews()
    {
        return $this->em->getRepository(View::class)->findAll();
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
     * @return \App\Entity\Room|null|object
     */
    public function getRoomForUser(User $user)
    {
        $zulu = $this->em->getRepository(Zulu::class)->findOneBy(['lockedBy' => $user->getUsername()]);

        return ($zulu) ? $zulu->getRoom() : null;
    }
}
