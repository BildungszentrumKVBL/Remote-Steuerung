<?php

namespace App\Twig;

use App\Entity\Room;
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

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_views', [$this, 'getViews']),
            new TwigFunction('get_next_view', [$this, 'getNextView']),
            new TwigFunction('get_previous_view', [$this, 'getPreviousView']),
            new TwigFunction('get_room_for_user', [$this, 'getRoomForUser']),
        ];
    }

    /**
     * @return View[]
     */
    public function getViews(): array
    {
        return $this->em->getRepository(View::class)->findAll();
    }

    public function getNextView(View $view): View
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

    public function getRoomForUser(User $user): ?Room
    {
        $zulu = $this->em->getRepository(Zulu::class)->findOneBy(['lockedBy' => $user->getUsername()]);

        return null !== $zulu ? $zulu->getRoom() : null;
    }
}
