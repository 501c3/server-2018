<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/14/17
 * Time: 9:28 AM
 */

namespace App\Security;



use App\Form\Security\LoginFormType;
use App\Repository\Access\SessionsRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;


class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var RouterInterface
     */

    private $router;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var SessionsRepository
     */
    private $sessionsRepository;

    private $user;

    public function __construct(FormFactoryInterface $formFactory,
                                RouterInterface $router,
                                UserPasswordEncoderInterface $passwordEncoder,
                                TokenStorageInterface $tokenStorage,
                                SessionsRepository $sessionsRepository)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenStorage = $tokenStorage;
        $this->sessionsRepository = $sessionsRepository;
    }

    public function supports(Request $request)
    {
        return $request->getPathInfo() == '/login' && $request->isMethod('POST');
    }


    public function getCredentials(Request $request)
    {

           $form = $this->formFactory->create(LoginFormType::class);
           $form->handleRequest($request);
           $data = $form->getData();
           $request->getSession()->set(
               Security::LAST_USERNAME,
               $data['_username']
           );
           return $data;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];
        $user=$userProvider->loadUserByUsername($username);
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
       $password = $credentials['_password'];
       if ($this->passwordEncoder->isPasswordValid($user, $password)) {
           return true;
       }
       return false;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $response = new RedirectResponse($this->router->generate('main_index'));
        return $response;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('security_login');
    }

}