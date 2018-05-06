<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 1/19/18
 * Time: 11:41 PM
 */

namespace App\Security;


use App\Entity\Access\User;
use App\Repository\Access\SessionsRepository;
use App\Repository\Access\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthAuthenticator extends SocialAuthenticator
{
    /**
     * @var ClientRegistry
     */
    private $registry;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var
     */
    private $client;

    /**
     * @var string
     */
    private $authenticator;
    /**
     * @var SessionsRepository
     */
    private $sessions;


    private $user;

    public function __construct(ClientRegistry $registry,
                                UserRepository $repository,
                                SessionsRepository $sessions,
                                RouterInterface $router)
    {
        $this->registry = $registry;
        $this->repository = $repository;
        $this->router = $router;
        $this->sessions = $sessions;
    }

    private function connectsSocialMedia(Request $request)
    {
        $matches=[];
        $result=preg_match('/\/connect\/(?P<provider>\w+)\/check/',$request->getPathInfo(),$matches);
        return $result?$matches['provider']:false;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        if ($authException instanceof AuthenticationCredentialsNotFoundException){
            // TODO:$this->client->redirect();
        }
    }

    public function supports(Request $request)
    {

        if ($authenticator=$this->connectsSocialMedia($request)) {
            $this->authenticator=$authenticator;
            $this->client=$this->registry->getClient($authenticator.'_main');
            return true;
        }
        return false;
    }

    public function getCredentials(Request $request)
    {
        $credentials=$this->fetchAccessToken($this->client);
        return $credentials;
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null|object|UserInterface
     * @throws AuthenticationException
     * @throws OptimisticLockException
     * @throws ORMException
     */

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
       $OAuthUser=$this->client->fetchUserFromToken($credentials);
       $user=$this->repository->loadOrCreateUser($OAuthUser, $this->authenticator, $credentials);
       $this->user=$user;
       return $user;
    }


    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {

       $request->getSession()->getFlashBag()->add('error',$exception->getMessage());
       $response = new RedirectResponse("/message");
       return $response;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function supportsRememberMe()
    {
        return true;
    }
}