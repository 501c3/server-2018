<?php

namespace App\Repository\Sales;

use App\Entity\Sales\Inventory;
use App\Entity\Sales\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;


/**
 * InventoryRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct( $registry, Inventory::class );
    }

    public function fetchInventory(Tag $tag)
    {
        $qb = $this->createQueryBuilder('inventory')
                    ->select('inventory','tag')
                    ->leftJoin('inventory.tag','tag')
                    ->where('tag=:tag');
        $query = $qb->getQuery();
        $query->setParameter('tag',$tag);
        $result = $query->getResult();
        return $result;
    }



    public function getEntityManager()
    {
        return parent::getEntityManager();
    }
}
