<?php

namespace App\Repository\Models;

use App\Entity\Models\Event;
use App\Entity\Models\Model;
use App\Entity\Models\Player;
use App\Entity\Models\Value;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * EventRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, Event::class);
    }

    public function getEntityManager()
    {
        return parent::getEntityManager();
    }

    private function qbEventBuilder()
    {
        $qb = $this->createQueryBuilder('event');
        $qb->select('event','value','domain','etag')
            ->join('event.model','model')
            ->join('event.value','value')
            ->join('value.domain','domain')
            ->join('event.tag','etag')
            ->where('model=:model')
            ->orderBy('event.id','ASC')
            ->addOrderBy('domain.id','ASC')
            ->addOrderBy('value.id','ASC');
        return $qb;
    }

    public function fetchEvents(Model $model)
    {
        $qb=$this->qbEventBuilder();
        $query=$qb->getQuery();
        $query->setParameter(':model',$model);
        return $query->getResult(Query::HYDRATE_ARRAY);
    }


}
