<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/14/18
 * Time: 12:52 PM
 */

namespace App\Repository\Competition;

use App\Entity\Competition\Competition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, Competition::class);
    }

    public function getEntityManager()
    {
        return parent::getEntityManager();
    }

    public function fetchCompetitionModelIds($competitionName)
    {
        $qb = $this->createQueryBuilder('competition');
        $qb->select('competition','subevent.modelId')
            ->leftJoin('competition.subevent','subevent')
            ->where('competition.name=:name')
            ->groupBy('competition.subevent.modelId');
        $query=$qb->getQuery();
        $query->setParameter('competition.name=:name',$competitionName);
        $result=$query->getResult();
        return $result;
    }
}