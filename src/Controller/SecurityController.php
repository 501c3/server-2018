<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/10/17
 * Time: 5:55 PM
 */

namespace App\Controller;


use App\Entity\Access\User;
use App\Form\Security\LoginFormType;
use App\Form\Security\RecoverFormType;
use App\Form\Security\RegisterFormType;
use App\Form\Security\ResetFormType;
use App\Security\LoginFormAuthenticator;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends Controller
{
    const UNKNOWN_EMAIL = 'This email does not correspond to a registered user.';
    const PASSWORD_RESET = 'Password Reset Request from Georgia DanceSport';
    const TOKEN_MISMATCH = 'Token does not match or is expired.';
    const RECAPTCHA_ERROR = 'The reCAPTCHA wasn\'t entered correctly.  Go back and try again.';
    /**
     * @var GuardAuthenticatorHandler
     */
    private $authenticatorHandler;
    /**
     * @var LoginFormAuthenticator
     */
    private $loginFormAuthenticator;


    public function __construct(GuardAuthenticatorHandler $authenticatorHandler,
                                LoginFormAuthenticator $loginFormAuthenticator)
    {
        $this->authenticatorHandler = $authenticatorHandler;
        $this->loginFormAuthenticator = $loginFormAuthenticator;
    }


    /**
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginFormType::class, [
                        '_username' => $lastUsername,
                        '_password' => PasswordType::class
                        ]);

        return $this->render('security/login.html.twig',
                    ['form' => $form->createView(),
                     'error'=> $error]);
    }


    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
        new \Exception("This should not be reached.");
    }


    /**
     * @Route("/register", name="security_register")
     */
    public function register(Request $request)
    {
        if($this->getParameter('recaptcha')){
            $recaptcha= new ReCaptcha($this->getParameter(recaptcha_private));
            $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
            if(!$resp->isSuccess()){
                $errors = $resp->getErrorCodes();
                return $this->render('security/register.html.twig',
                    ['form'=>$form->createView(),
                    'reCAPTCHA_ERROR'=> true]);
            }
        }
        $form = $this->createForm(RegisterFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $user->setRoles(['ROLE_USER']);
            $em = $this->getDoctrine()->getManager('access');
            $em->persist($user);
            $flashBag=$request->getSession()->getFlashBag();
         try {
             $em->flush();
             // TODO: Send email to acknowledge registration.
             return $this->authenticatorHandler
                 ->authenticateUserAndHandleSuccess(
                     $user,
                     $request,
                     $this->loginFormAuthenticator,
                     'access'
                 );
            }catch(UniqueConstraintViolationException $e){
                $matches=[];
                $result=preg_match('/Duplicate entry (?P<field>\'\s+\')/',$e->getMessage(), $matches);
                if($result){
                    $flashBag->add('error',sprintf('Duplicate entry %s',$matches['field']));
                } else {
                    $flashBag->add('error', $e->getMessage());
                }
                return $this->render('security/register.html.twig',['form'=>$form->createView()]);
            }catch(\Exception $e){
                $class = get_class($e);
                $flashBag->add('error', sprintf('Unhandled exception : %s', $class));
                $flasBag->add('error', sprintf('message:\n%s',$e->getMessage()));
                return $this->render('security/message.html.twig');
            }
        }
        return $this->render('security/register.html.twig',['form'=>$form->createView()]);
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/recover", name="security_recover")
     *
     */
    public function recoverAction(Request $request)
    {
        $form = $this->createForm(RecoverFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager('access');
            $user = $em->getRepository(User::class)
                        ->findOneBy(['email'=>$data['_email']]);
            if ($user){
                $url = $request->getSchemeAndHttpHost().'/reset';
                $this->emailPasswordReset($user, $em, $url);
                $user->setPasswordRequestedAt(new \DateTime('now'));
                $em->flush();
                return $this->render('security/check_email.html.twig');
            } else {
                return $this->render('security/recover.html.twig', [
                    'form' => $form->createView(),
                    'error' => ['messageKey'=> self::UNKNOWN_EMAIL,
                                'messageData'=>[]]
                ]);
            }
        }
        return $this->render('security/recover.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    private function emailPasswordReset(User $user, EntityManagerInterface $em, $url){
        $token = uniqid();
        $user->setConfirmationToken($token)
             ->setPasswordRequestedAt(new \DateTime('now'));
        $em->flush();
        $urlAndToken = $url.'?token='.$token;
        $attachment = \Swift_Image::fromPath(__DIR__.'/../../assets/images/dancers.png')
                        ->setDisposition('inline');
        $attachment->getHeaders()->addTextHeader('Content-ID', '<dancers>');
        $attachment->getHeaders()->addTextHeader('X-Attachment-Id', 'dancers');
        $message = (new \Swift_Message('Password Reset Request'));
        $cid = $message->embed($attachment);
        $message->setFrom(['mgarber@georgiadancesport.org'=>'Mark Garber'])
                ->setTo([$user->getEmailCanonical()=>$user->getEmailName()])
                ->setCc(['mgarber356@comcast.net'=>'Mark Garber'])
                ->setBody(
                    $this->renderView(
                            'security/reset_email.html.twig',
                            ['cid'=>$cid,
                             'link'=>$urlAndToken]),
                            'text/html'
                        );
        return $this->get('mailer')->send($message);

    }

    /**
     * @param Request $request
     *
     * @Route("/reset",
     *     name="security_reset",
     *     methods="GET|POST"
     * )
     */
    public function resetAction(Request $request)
    {
        parse_str($request->getQueryString(),$query);
        $em=$this->getDoctrine()->getManager('access');
        $repository=$em->getRepository('Access:User');
        $form = $this->constructFormIfQuery($query, $repository, $request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user= $repository->findOneBy(['confirmationToken'=>$data['_token']]);
            if($user){
                $dateDifference=$user->getPasswordRequestedAt()->diff(new \DateTime('now'));
                $password = $data['_password'];
                $user->setPlainPassword($password);
                $em->flush();
            } else {

            }
            // TODO: Send email to acknowledge registration and warn of change.
            return $this->authenticatorHandler
                ->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $this->loginFormAuthenticator,
                    'access'
                );
        }
        return $this->render('security/reset.html.twig',
                            ['form'=>$form->createView()]);
    }

    /**
     * @Route("/message",
     *        name="security_message",
     *        methods="GET")
     *
     *
     * @return Response
     */
    public function message():Response{
        return $this->render('security/message.html.twig',[]);
    }

    private function constructFormIfQuery(array $query, EntityRepository $repository, Request $request) {
        if(count($query)) {
            $user = $repository->findOneBy( ['confirmationToken' => $query['token']] );
            $form = $this->createForm( ResetFormType::class, ['_username' => $user->getUsername(),
                                                                   '_token' => $user->getConfirmationToken()]);
            return $form;
        } else {
            $form = $this->createForm( ResetFormType::class);
            $form->handleRequest($request);
            return $form;
        }

    }


    private function captchaverify($recaptcha){
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "secret"=>"6LeTXQgUAAAAALExcpzgCxWdnWjJcPDoMfK3oKGi","response"=>$recaptcha));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        return $data->success;
    }
}