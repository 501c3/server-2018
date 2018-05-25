<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/21/18
 * Time: 3:52 PM
 */

namespace App\Entity\Sales\Client;


use App\Entity\Models\Value;
use Doctrine\Common\Collections\ArrayCollection;

class Qualification
{
    private $collection;

    public function __construct()
    {
        $this->collection = new ArrayCollection();
    }

    public function add(Value $value)
    {
        $this->collection->set( $value->getDomain()->getName(), $value );
    }


    public function set(ArrayCollection $collection)
    {
        $this->collection = $collection;
    }


    public function match(array $description): bool
    {
        foreach($description as $key=>$idName){
            /** @var Value $value */
            $value=$this->collection->get($key);
            if(is_numeric($idName)) {
               if($value->getid()!=intval($idName)){
                   return false;
               }
            } else {
               if($value->getName()!=$idName){
                   return false;
               }
            }

        }
        return true;
    }

   public function toJson(bool $debug = false): string
   {
       return json_encode($this->toArray($debug));
   }

    /**
     * @param bool $debug
     * @return array
     *
     * $debug = true will return proper names instead of id.
     */
   public function toArray(bool $debug=false):array
   {
       $return=[];
       $this->collection->first();
       /** @var Value $value */
       $value = $this->collection->current();
       while($value){
           $return[$value->getDomain()->getName()]=$debug?$value->getName():$value->getId();
           $value = $this->collection->next();
       }
       return $return;
   }
}