<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/18/17
 * Time: 10:25 PM
 */

namespace App\Controller\Access;


use App\Entity\Access\User;
use App\Form\RecoverFormType;
use App\Form\RegisterFormType;
use App\Repository\Access\UserRepository;
use App\Security\LoginFormAuthenticator;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class UserController extends Controller
{

    /**
     * @var GuardAuthenticatorHandler
     */
    private $authenticatorHandler;
    /**
     * @var LoginFormAuthenticator
     */
    private $authenticator;
    /**
     * @var Paginator
     */
    private $paginator;
    /**
     * @var UserRepository
     */
    private $userRepository;


    public function __construct(GuardAuthenticatorHandler $authenticatorHandler,
                                LoginFormAuthenticator $authenticator,
                                UserRepository $userRepository,
                                PaginatorInterface $paginator)
    {
        $this->authenticatorHandler = $authenticatorHandler;
        $this->authenticator = $authenticator;
        $this->paginator = $paginator;
        $this->userRepository = $userRepository;
    }


    /**
     * @Route("/admin/user", name="access_list_users")
     *
     *
     * @param UserRepository $userRepository
     * @return string|\Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request){
        // TODO: List all users
        $qb = $this->userRepository->createQueryBuilder('user');
        $query=$qb->getQuery();
        $pagination=$this->paginator->paginate($query, $request->query->getInt('page',1),20);
        return $this->render('access/list.html.twig',['pagination'=>$pagination]);
    }

}