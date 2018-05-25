<?php

namespace App\Repository\Models;

use App\Entity\Models\Model;
use App\Entity\Models\Value;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;


/**
 * ValueRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class ValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, Value::class);
    }

    public function getEntityManager()
    {
        return parent::getEntityManager(); // TODO: Change the autogenerated stub
    }

    public function fetchDomainValues(Model $model)
    {
        $qb=$this->createQueryBuilder('value');
        $qb->select('value','domain')
            ->leftJoin('value.model','model')
            ->leftJoin('value.domain','domain')
            ->where('model=:model');
        $query=$qb->getQuery();
        $query->setParameter(':model',$model);
        $result =$query->getResult();
        return $result;
    }

    public function fetchAllDomainValues()
    {
        $qb=$this->createQueryBuilder('value');
        $qb->select('value','domain')
            ->leftJoin('value.domain','domain');
        $query=$qb->getQuery();
        $result =$query->getResult();
        return $result;
    }

    public function fetchAllGenreValues()
    {
        $qb=$this->createQueryBuilder('value');
        $qb->select('value','domain')
            ->leftJoin('value.domain','domain')
            ->where('domain.name="style"')
            ->orWhere('domain.name="substyle"');
        $query=$qb->getQuery();
        $result=$query->getResult();

    }
}
