<?php

namespace App\Repository\Models;

use App\Entity\Models\Model;
use App\Entity\Models\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;


/**
 * PlayerRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, Player::class);
    }

    public function getEntityManager()
    {
        return parent::getEntityManager();
    }

    private function qbPlayerBuilder()
    {
        $qb=$this->createQueryBuilder('player');
        $qb->select('player','value','domain','event')
            ->join('player.value','value')
            ->join('value.domain', 'domain')
            ->join('player.event','event')
            ->where('player.model=:model')
            ->orderBy('player.id','ASC')
            ->addOrderBy('domain.id','ASC')
            ->addOrderBy('value.id','ASC');
        return $qb;
    }

    public function fetchPlayers(Model $model)
    {
        $qb=$this->qbPlayerBuilder();
        $query = $qb->getQuery(Query::HYDRATE_ARRAY);
        $query->setParameter(':model',$model);
        return $query->getResult(Query::HYDRATE_ARRAY);
    }
}
