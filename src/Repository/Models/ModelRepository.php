<?php

namespace App\Repository\Models;

use App\Entity\Models\Model;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
/**
 * ModelRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class ModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, Model::class);
    }

    public function fetchModelsById()
    {
        $models=$this->findAll();
        $result=[];
        foreach($models as $model) {
            $result[$model->getId()]=$model;
        }
        return $result;
    }

    public function fetchModelsByName()
    {
        $models=$this->findAll();
        $result=[];
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
