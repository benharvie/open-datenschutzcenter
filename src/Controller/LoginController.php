<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\Auth0Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

class LoginController extends AbstractController
{
    /**
     * @Route("/login/auth0_login", name="login_auth0")
     */
    public function index(ClientRegistry $clientRegistry): Response
    {
      return $clientRegistry->getClient('auth0_main')->redirect(['user']);
    }
    /**
     * @Route("/login/auth0_login/check", name="connect_auth0_check")
     */
    public function check(ClientRegistry $clientRegistry, Request $request)
    {
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

        /** @var Auth0Client $client */
        $client = $clientRegistry->getClient('auth0_main');

        try {

            $user = $client->fetchUser();

            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            var_dump($user->getEmail()); die;
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            var_dump($e->getMessage()); die;
        }
    }
    /**
     * @Route("/logout_keycloak", name="logout_keycloak")
     */
    public function logout(ClientRegistry $clientRegistry, Request $request)
    {
        $url = $this->getParameter('KEYCLOAK_URL')
            .'/realms/'.$this->getParameter('KEYCLOAK_REALM')
            .'/protocol/openid-connect/logout?redirect_uri='.$this->generateUrl('dashboard',[],UrlGenerator::ABSOLUTE_URL);
        return $this->redirect($url);

    }
}
