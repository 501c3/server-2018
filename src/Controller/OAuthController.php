<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 1/19/18
 * Time: 9:22 PM
 */

namespace App\Controller;


use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class OAuthController extends Controller
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/{provider}", name="connect_oauth2")
     */
    public function connect(Request $request, string $provider, ClientRegistry $registry)
    {
        // will redirect to google, facebook, linkedin, paypal  or any number of social authentication providers!
        $state = $this->randomString();
        $request->getSession()->set(OAuth2Client::OAUTH2_SESSION_STATE_KEY,$state);
        $client=$registry->getClient($provider.'_main'); // key used in knp.yaml
        switch($provider){
            case 'google':
            case 'facebook':
                return $client->redirect(['openid','email']);
            case 'linkedin':
                return $client->redirect(['r_basicprofile','r_emailaddress']);
            case 'paypal':
                return $client->redirect(['openid profile email']);
        }

    }

    /**
     * After going to Google, you're redirected back here
     * because this is the "redirect_route" you configured
     * in knp.yaml
     *
     * @param Request $request
     *
     * @Route("/connect/google/check", name="oauth2_google_check")
     */
    public function connectedGoogle(Request $request)
    {
        return $this->completeConnection($request);
    }

    /**
     * After going to Facebook you're redirected back here
     * because this is the "redirect_route" you configured
     * in knp.yaml
     *
     * @param Request $request
     *
     * @Route("/connect/facebook/check", name="oauth2_facebook_check")
     */
    public function connectedFacebook(Request $request)
    {
        return $this->completeConnection($request);
    }

    /**
     * After going to LinkedIn you're redirected back here
     * because this is the "redirect_route" you configured
     * in knp.yaml
     *
     * @Route("/connect/linkedin/check", name="oauth2_linkedin_check")
     */
    public function connectedLinkedIn(Request $request)
    {
        return $this->completeConnection($request);
    }

    /**
     * After going to PayPal you're redirected back here
     * because this is the "redirect_route" you configured
     * in knp.yaml
     *
     * @Route("/connect/paypal/check", name="oauth2_paypal_check")
     */
    public function connectedPayPal(Request $request)
    {
        return $this->completeConnection($request);
    }

    private function completeConnection(Request $request)
    {
        $token=$this->get('security.token_storage')->getToken();
        $request->getSession()->set('security.token_storage',$token);
        return $this->forward('App\Controller\MainController::administration', ['request'=>$request]);
    }

    private function randomString( $length = 8 ) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $str = substr( str_shuffle( $chars ), 0, $length );
        return $str;
    }

}