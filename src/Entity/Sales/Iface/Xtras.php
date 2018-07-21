<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 7/16/18
 * Time: 10:53 AM
 */

namespace App\Entity\Sales\Iface;


class Xtras
{
    private $id;

    private $currency;

    private $inventory=[];

    private $order = [];

    public function __construct(string $currency)
    {
        $this->currency=$currency;
    }

    public function hasId():bool
    {
        return $this->id?true:false;
    }

    public function setId(int $id):Xtras
    {
        $this->$id = $id;
        return $this;
    }

    public function setInventory(int $id, string $description, float $unitPrice):Xtras
    {
        $this->inventory[$id]=['description'=>$description, 'unitPrice'=>$unitPrice];
        return $this;
    }

    public function setOrder(int $id, int $qty):Xtras
    {
        $this->order[$id]=$qty;
        return $this;
    }

    public function preJSON()
    {
        $data = [];
        foreach($this->inventory as $id=>$spec){
            $data[$id]=$spec;
            $data[$id]['qty']=isset($this->order[$id])?$this->order[$id]:null;
            $data[$id]['ext']=isset($this->order[$id])?$this->order[$id]*$spec['unitPrice']:null;
        }
        return $data;
    }

    public function toArray()
    {
        return ['id'=>$this->id,
                'currency'=>$this->currency,
                'inventory'=>$this->inventory,
                'order'=>$this->order];
    }

    public function fromArray($data)
    {
        $this->id = $data['id'];
        $this->currency = $data['currency'];
        $this->inventory = $data['inventory'];
        $this->order = $data['order'];
    }


    public function describe()
    {
        $description = [];
        /** @var float $total */
        $total = 0.0;
        foreach($this->order as $id=>$qty) {
            $unitPrice = $this->inventory[$id]['unitPrice'];
            $ext = $qty*$unitPrice;
            $description[$id]=[ 'qty'=>$qty,
                                'description'=>$this->inventory[$id]['description'],
                                'unit price'=> $unitPrice,
                                'ext'=>$ext];
            $total+=$ext;
        }
        return ['order'=>$description,
                'total'=>$total,
                'currency'=>$this->currency];

    }
}