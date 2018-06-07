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

use App\Entity\Competition\Event;
use App\Entity\Competition\Model;
use App\Entity\Competition\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, Event::class);
    }

    public function fetchPlayerEvents(Model $model, Player $player)
    {
        $qb=$this->createQueryBuilder('event');
        $qb->select('model','player','event')
            ->leftJoin('event.model','model')
            ->leftJoin('event.player','player')
            ->where('model=:model')
            ->andWhere('player=:player');
        $query=$qb->getQuery();
        $query->setParameters([':model'=>$model,':player'=>$player]);
        $result= $query->getResult();
        return $result;
    }

    public function fetchEventsPreJSON(Model $model, Player $player)
    {
        $events = $this->fetchPlayerEvents($model,$player);
        $preJSON = [];
        /** @var Event $event */
        foreach($events as $event){
            $preJSON[$event->getId()]=$event->getValue();
        }
        return $preJSON;
    }

    public function getEntityManager()
    {
        return parent::getEntityManager();
    }

}