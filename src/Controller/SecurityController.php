<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController.
 *
 * This controller handles security related requests for symfony. Such as **login**, **logout** and **login check**.
 */
class SecurityController extends AbstractController
{
    /**
     * This route serves the login page. If the user is authenticated, he will be redirected to the index page.
     *
     * @Route("/login", name="security_login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @param EntityManagerInterface $em
     *
     * @return Response
     */
    public function loginAction(AuthenticationUtils $authenticationUtils, EntityManagerInterface $em): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $users = $em->getRepository(User::class)->findAll();

        return $this->render(
            'security/login.html.twig',
            [
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
                'users'         => $users,
            ]
        );
    }

    /**
     * This is an empty route, symfony handles the logout process itself.
     *
     * @Route("/logout", name="security_logout", options={"expose": true})
     */
    public function logoutAction()
    {
    }
}
