<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/13/18
 * Time: 8:35 PM
 */

namespace App\Entity\Sales\Iface;


use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\PricingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ParticipantList
{
    /**
     * @var Tag
     */

    const DISPLAY_NAME_NORMAL = 1;
    const DISPLAY_NAME_LAST = 2;

    private $sort;

    private $display;

    private $collection;

    public function __construct(int $display)
    {
        $this->display=$display;
        $this->collection = new ArrayCollection();

    }

    public function add(array $data) : ParticipantList
    {
        $this->collection->add($data);
        return $this;
    }

    public function describe()
    {
        return $this->collection->toArray();
    }

    public function preJSON()
    {
        /** @var Collection $list */
        $list = $this->collection->map(function($item){return ['first'=>$item['first'],
                                                               'last'=>$item['last'],
                                                               'id'=>$item['id']];});
        $arrayCollection = new ArrayCollection();
        /** @var \ArrayIterator $iterator */
        $iterator = $list->getIterator();
        $current = $iterator->current();
        while($current){
            switch ($this->display){
                case self::DISPLAY_NAME_NORMAL:
                    $arrayCollection->set($current['first'].' '.$current['last'],$current['id']);
                    break;
                case self::DISPLAY_NAME_LAST:
                    $arrayCollection->set($current['last'].', '.$current['first'],$current['id']);
            }
            $iterator->next();
            $current = $iterator->current();
        }
        $iterator=$arrayCollection->getIterator();
        $iterator->ksort();
        $result = [];
        $current = $iterator->current();
        while($current){
            $result[$iterator->key()]=$current;
            $iterator->next();
            $current=$iterator->current();
        }
        return $result;
    }

}