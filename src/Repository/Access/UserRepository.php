<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 1/13/18
 * Time: 11:25 AM
 */

namespace App\Repository\Access;
use App\Entity\Access\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\PaypalClient;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\LinkedInResourceOwner;
use Stevenmaguire\OAuth2\Client\Provider\PaypalResourceOwner;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserRepository extends ServiceEntityRepository implements UserProviderInterface, UserLoaderInterface
{
    const NOT_AUTHENTICATED_PAYPAL = 'You did not authenticate with PayPal.',
          NOT_AUTHENTICATED_LINKEDIN = 'You did not authenticate with LinkedIn.',
          NOT_AUTHENTICATED_FACEBOOK = 'You did not authenticate with Facebook.',
          NOT_AUTHENTICATED_GOOGLE = 'You did not authenticate with Google.',
          UNABLE_TO_AUTHENTICATE = 'Unable to authenticate.';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, User::class );
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return parent::getEntityManager();
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return Object
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username) : Object
    {
        return $this->findOneBy(['username'=>$username]);
    }


    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $social The social
     * @param string $id
     * @return Object
     *
     * @throws AuthenticationCredentialsNotFoundException if the user is not found
     */
    public function loadUserBySocial($social, $id): Object
    {
        return $this->findOneBy(['authenticator'=>$social,
                                 'authenticatorId'=>$id]);
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     *
     * @param UserInterface $user
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the user is not supported
     */
    public function refreshUser(UserInterface $user)
    {
       return $user;
    }


    private function throwNotAuthenticatedException($authenticator){
        switch($authenticator){
            case 'google':
                throw new AuthenticationException(self::NOT_AUTHENTICATED_GOOGLE);
            case 'facebook':
                throw new AuthenticationException(self::NOT_AUTHENTICATED_FACEBOOK);
            case 'linkedIn':
                throw new AuthenticationException(self::NOT_AUTHENTICATED_LINKEDIN);
            case 'payPal':
                throw new AuthenticationException(self::NOT_AUTHENTICATED_PAYPAL);
            default:
                throw new AuthenticationException(self::UNABLE_TO_AUTHENTICATE);
        }
    }


    /**
     * @param Object $OAuthUser
     * @param string $authenticator
     * @param string $id
     * @param string $credentials
     * @return User|null|object
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    private function updatePreviousOrCreateNew(Object $OAuthUser, string $authenticator, string $id, string $credentials){
        $em = $this->getEntityManager();
        $OAuthEmail = $OAuthUser->getEmail();
        if($previousUser= $this->findOneBy(['emailCanonical'=>$OAuthEmail])){
            $previousAuthenticator=$previousUser->getAuthenticator();
            if (!is_null($previousAuthenticator) && $previousAuthenticator != $authenticator) {
                throw new AuthenticationException('Previous authentication was with another provider.');
            }
            $previousUser->setAuthenticator($authenticator)
                         ->setAuthenticatorId($id);
            $em->flush();
            return $previousUser;
        } else {
            $user = new User();
            $token=strlen($credentials)<255?$credentials:null;
            $user->setFirst($OAuthUser->getFirstName())
                 ->setLast($OAuthUser->getLastName())
                 ->setUsername($OAuthUser->getEmail())
                 ->setEmail($OAuthUser->getEmail())
                 ->setAuthenticator($authenticator)
                 ->setAuthenticatorId($OAuthUser->getId())
                 ->setConfirmationToken($token)
                 ->setAccessToken($token)
                 ->setRefreshToken($token)
                ->setEnabled(true)
                ->setRoles(['ROLE_USER']);
            $em->persist($user);
            $em->flush();
            return $user;
        }
    }


    /**
     * @param $OAuthUser
     * @param string $authenticator
     * @param string $credentials
     * @return User|null|object
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function loadOrCreateUser(Object $OAuthUser, string $authenticator, string $credentials){
        $OAuthId = $OAuthUser->getId();
        if ($OAuthUser instanceof GoogleUser ){
            if ($authenticator == 'google'){
                return $this->updatePreviousOrCreateNew($OAuthUser, $authenticator, $OAuthId, $credentials);
            } else $this->throwNotAuthenticatedException($authenticator);
        }
        if ($OAuthUser instanceof FacebookUser){
            if ($authenticator == 'facebook'){
                return $this->updatePreviousOrCreateNew($OAuthUser, $authenticator, $OAuthId, $credentials);
            } else $this->throwNotAuthenticatedException($authenticator);
        }

        if ($OAuthUser instanceof LinkedInResourceOwner){
            if ($authenticator == 'linkedin'){
                return $this->updatePreviousOrCreateNew($OAuthUser, $authenticator, $OAuthId, $credentials);
            } else $this->throwNotAuthenticatedException($authenticator);
        }
        /* TODO: Delete */
        if ($OAuthUser instanceof PaypalResourceOwner) {
            if ($authenticator == 'paypal'){
                return $this->updatePreviousOrCreateNew($OAuthUser, $authenticator, $OAuthId, $credentials);
            } else $this->throwNotAuthenticatedException($authenticator);
        }
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class == 'App\Entity\Access\User';
    }
}