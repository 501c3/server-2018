<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/14/18
 * Time: 12:53 PM
 */

namespace App\Repository\Competition;


use App\Entity\Competition\Model;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, Model::class);
    }

    public function fetchModelById() : array
    {
        $result = [];
        $models = $this->findAll();
        /** @var Model $model */
        foreach ($models as $model) {
            $result[$model->getId()]=$model;
        }
        return $result;
    }

    public function fetchModelsByName()
    {
        $models=$this->findAll();
        $result=[];
        /** @var Model $model */
        foreach($models as $model) {
            $result[$model->getName()]=$model;
        }
        return $result;
    }

    public function getEntityManager()
    {
        return parent::getEntityManager();
    }
}