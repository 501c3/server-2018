<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/18/18
 * Time: 5:38 PM
 */

namespace App\Entity\Sales\Iface;


use Doctrine\Common\Collections\ArrayCollection;

class PlayerList
{
    /** @var ArrayCollection  */
    private $playerList;

    public function __construct()
    {
        $this->playerList = new ArrayCollection();
    }

    public function addPlayer(int $id, array $data)
    {
        $this->playerList->set($id, $data);
    }

    public function count() {
        return $this->playerList->count();
    }

    public function containsKey($id) {
        return $this->playerList->containsKey($id);
    }

    private function adjustPosition(array $records){

        switch(count($records)){
            case 1:
                $record0=current($records);
                $record0Key = key($records);
                $record0['id']=$record0Key;
                return $record0;
            case 2:
                $record0=current($records);
                $record0Key = key($records);
                $record1=next($records);
                $record1Key= key($records);
                $record0['id']=$record0Key;
                $record1['id']=$record1Key;
                switch($record0['typeA']){
                    case 'Amateur':
                        switch($record1['typeA']){
                            case 'Professional':
                                return [$record0,$record1];
                        }
                        break;
                    case 'Professional':
                        switch($record1['typeA']){
                            case 'Amateur':
                                return [$record1,$record0];
                        }
                }

                switch($record0['typeB']) {
                    case 'Student':
                        switch($record1['typeB']){
                            case 'Teacher':
                                return [$record0,$record1];
                        }
                        break;
                    case 'Teacher':
                        switch($record1['typeB']){
                            case 'Student':
                                return [$record1,$record0];
                        }
                }

                switch($record0['sex']) {
                    case 'M':
                        switch($record1['sex']) {
                            case 'F':
                                return [$record1,$record0];
                        }
                    case 'F':
                        switch($record1['sex']) {
                            case 'M':
                                return [$record0,$record1];
                        }
                }


                $strcmpLast=strcmp(strtolower($record0['last']),strtolower($record1['last']));

                if($strcmpLast<0) {
                    return [$record0,$record1];
                }

                if($strcmpLast>0) {
                    return [$record1,$record0];
                }

                $strcmpFirst = strcmp(strtolower($record0['first']),strtolower($record1['first']));

                if($strcmpFirst<0) {
                    return [$record0,$record1];
                }

                if($strcmpFirst>0) {
                    return [$record1, $record0];
                }

                return [$record0,$record1];
        }

        $result = [];
        foreach($records as $id=>$record){
            $record['id']=$id;
            array_push($result,$record);
        }
        return $result;
    }

    public function preJSON()
    {
        $organizer = new ArrayCollection;
        $iterator = $this->playerList->getIterator();
        $records = $iterator->current();
        while($records){
            $teamId = $iterator->key();
            $adjusted = $this->adjustPosition($records);
            $key = $adjusted[0]['last'].', '.$adjusted[0]['first'];
            $organizer->set("$key|$teamId",$adjusted);
            $iterator->next();
            $records = $iterator->current();
        }

        $display = new ArrayCollection();
        $iterator2 = $organizer->getIterator();
        $iterator2->ksort();
        $records2 = $iterator2->current();
        while($records2){
            $key2 = $iterator2->key();
            list($name,$id)=preg_split('/\|/',$key2);
            $display->set($id,$records2);
            $iterator2->next();
            $records2 = $iterator2->current();
        }
        return $display->toArray();
    }

}