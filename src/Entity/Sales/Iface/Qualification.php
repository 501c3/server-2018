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

namespace App\Entity\Sales\Iface;


use App\Entity\Models\Value;
use Doctrine\Common\Collections\ArrayCollection;

class Qualification
{
    const DOMAIN_NAME_TO_VALUE_ID = 0;
    const DOMAIN_NAME_TO_VALUE_NAME = 1;

    private $collection;

    public function __construct()
    {
        $this->collection = new ArrayCollection();
    }

    public function add(Value $value)
    {
        $domain = $value->getDomain()->getName();
        $key = ($domain == 'style'|| $domain == 'substyle')?'genre':$domain;
        $this->collection->set( $key,$value);
    }

    public function set(array $values)
    {
        foreach($values as $value)
        $this->add($value);
    }

    public function get(string $dom)
    {
        return $this->collection->get($dom);
    }

    public function toArray(int $identifierSpec): array
    {
        /** @var \ArrayIterator $iterator */
        $iterator=$this->collection->getIterator();
        $iterator->rewind();
        $result = [];
        while($iterator->valid()){
            $value = $iterator->current();
            $key=$iterator->key();
            switch($identifierSpec){
                case self::DOMAIN_NAME_TO_VALUE_ID:
                    $result[$key]=$value->getId();
                case self::DOMAIN_NAME_TO_VALUE_NAME:
                    $result[$key]=$value->getName();
            }
            $iterator->next();
        }
        return $result;
    }

}