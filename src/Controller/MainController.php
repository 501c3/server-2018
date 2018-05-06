<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/18/17
 * Time: 10:46 PM
 */

namespace App\Controller;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class MainController extends Controller
{

    /**
     * @Route("/", name="main_index")
     * @return Response
     */
    public function index(Request $request)
    {
       if($this->has('security.token_storage')) {
            $user = $this->getUser();
            $token = $this->get( 'security.token_storage' )->getToken();
            if($user instanceof UserInterface){
                $token->setUser($user);
            }
        }
        $user=$this->getUser();
        return $this->render('main/index.html.twig');
    }

    /**
     * @Route("/admin", name="main_admin")
     */
    public function administration(Request $request):Response
    {
       return $this->render('main/admin.html.twig');
    }

    /**
     * @Route("/sales", name="main_sales")
     * @return Response
     */
    public function competitions():Response
    {
        return $this->render('main/admin.html.twig');
    }


    /**
     * @return Response
     */
    public function certificates()
    {
        return $this->render('main/certificates.html.twig');
    }
}