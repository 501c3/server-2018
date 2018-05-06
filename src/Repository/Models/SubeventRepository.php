<?php

namespace App\Repository\Models;

use App\Entity\Models\Model;
use App\Entity\Models\Subevent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;


/**
 * SubeventRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class SubeventRepository extends ServiceEntityRepository
{
    private $recordCount;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, Subevent::class);
    }

    public function getEntityManager()
    {
        return parent::getEntityManager();
    }

    public function fetchSubevents(Model $model, array $values=[], int $offset=null, int $limit = null)
    {
       //$this->getEntityManager()->clear();
        $qb = $this->qbSubeventBuilder();
        if(!count($values)){
            $query=$qb->getQuery();
            $query->setParameter(':model',$model);
            $result=$query->getResult(Query::HYDRATE_ARRAY);
            return $result;
        }
        $qb->andWhere('value=:value');
        $value = array_shift($values);
        $query=$qb->getQuery();
        $query->setParameters([':model'=>$model, ':value'=>$value]);
        $subevents = $query->getResult();
        return $this->fetchSubeventsRecursive($model,$values,$subevents, $offset, $limit);
    }

    private function fetchSubeventsRecursive(Model $model, array $values, array $subevents,
                                             int $offset=null, int $limit=null)
    {
        $qb = $this->qbSubeventBuilder();
        if(!count($values)){
            $qb->andWhere('subevent IN (:subevents)');
            $query = $qb->getQuery();
            if($offset){
                $query->setFirstResult($offset)->setMaxResults($limit);
            }
            $query->setParameters([':model'=>$model,':subevents'=>$subevents]);
            $result=$query->getResult(Query::HYDRATE_ARRAY);
            return $result;
        }
        $qb->andWhere('value=:value')
           ->andWhere('subevent IN (:subevents)');
        $query = $qb->getQuery();
        $value = array_shift($values);
        $query->setParameters([':model'=>$model, ':value'=>$value, ':subevents'=>$subevents]);
        $nextSubevents=$query->getResult();
        return $this->fetchSubeventsRecursive($model,$values,$nextSubevents,$offset,$limit);
    }


    private function qbSubeventBuilder()
    {
        $qb = $this->createQueryBuilder('subevent');
        $qb->select('subevent','event','value','domain')
            ->leftJoin('subevent.event','event')
            ->leftJoin('event.model','model')
            ->leftJoin('subevent.value','value')
            ->leftJoin('value.domain','domain')
            ->where('model=:model')
            ->orderBy('subevent.id','ASC')
            ->addOrderBy('domain.id','ASC')
            ->addOrderBy('value.id','ASC');
        return $qb;
    }


}
