<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/18/17
 * Time: 8:20 PM
 */

namespace App\Doctrine;

use App\Entity\Access\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class HashPasswordListener implements EventSubscriberInterface
{
    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {

        $this->passwordEncoder = $passwordEncoder;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof User) {
            return;
        }

        $this->encodePassword( $entity );
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof User) {
            return;
        }

        $this->encodePassword( $entity );
        $em = $args->getEntityManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta,$entity);
   }

    public static function getSubscribedEvents()
    {
       return ['prePersist', 'preUpdate'];
    }

    /**
     * @param $entity
     */
    private function encodePassword($entity): void
    {
        $encoded = $this->passwordEncoder->encodePassword( $entity, $entity->getPlainPassword() );
        $entity->setPassword( $encoded );
    }
}