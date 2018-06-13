<?php

namespace App\Repository\Sales;

use App\Entity\Sales\Contact;
use App\Entity\Sales\Workarea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * ContactRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 * @Embedded\Embedded
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, Contact::class);
    }

    /**
     * @param Workarea $workarea
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function fetchContact(Workarea $workarea) {
        $qb=$this->createQueryBuilder('contact');
        $qb->select('contact','workarea')
           ->leftJoin('contact.workarea','workarea')
           ->where('workarea=:workarea');
        $query = $qb->getQuery();
        $query->setParameter(':workarea',$workarea);
        return $query->getSingleResult();
    }

    public function getEntityManager()
    {
        return parent::getEntityManager();
    }

}
