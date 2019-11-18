<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SecurityController.
 *
 * This controller handles security related requests for symfony. Such as **login**, **logout** and **login check**.
 */
class SecurityController extends Controller
{
    /**
     * This route serves the login page. If the user is authenticated, he will be redirected to the index page.
     *
     * @Route("/login", name="login_route")
     */
    public function loginAction(): Response
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            // redirect authenticated users to homepage
            return $this->redirect($this->generateUrl('homepage'));
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $users = $this->get('doctrine.orm.entity_manager')->getRepository(User::class)->findAll();

        return $this->render(
            'AppBundle:security:login.html.twig', array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
                'users'     => $users,
            )
        );
    }

    /**
     * This is an empty route, it exists in order for symfony to hook onto it.
     *
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

    /**
     * This is an empty route, symfony handles the logout process itself.
     *
     * @Route("/logout", name="logout_route", options={"expose": true})
     */
    public function logoutAction()
    {

    }
}
