<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/11/18
 * Time: 9:23 AM
 */

namespace App\Utils;


use App\Exceptions\GeneralException;
use App\Exceptions\MissingException;
use App\Subscriber\Status;
use App\Subscriber\StatusEvent;

class YamlTraverse
{

    private $linesToReport;
    private $startTime;
    private $eventDispatcher;

    protected function fields(array $data, array $position, array $acceptedKeys, bool $strict) {
        list($dataPart,$positionPart,$key,$keyPosition)
            = $this->current($data,$position);
        $keyedPosition = [];
        while($dataPart) {
            if(!in_array($key,$acceptedKeys)) {
                $acceptedKeysStr = join('","',$acceptedKeys);
                throw new GeneralException($key,$keyPosition,'expected "$acceptedKeyStr"',
                                            9000);
            }
            $keyedPosition[$key]=$positionPart;
            list($dataPart,$positionPart,$key,$keyPosition)
                =$this->next($data,$position);
        }
        if($strict){
            $diff = array_diff($acceptedKeys,array_keys($keyedPosition));
            if(count($diff)){
                throw new MissingException($diff,array_keys($position),9000);
            }

        }
        return ['data'=>$data,'position'=>$keyedPosition];
    }


    /**
     * @param $positionKey
     * @return bool|mixed
     */
    protected function lineNumber($positionKey)
    {
        $pos=[];
        preg_match('/R(?P<row>\d+)C(?P<col>\d+)/',$positionKey, $pos);
        return isset($pos['row'])?$pos['row']:false;
    }



    /**
     * @param $data
     * @param $position
     * @return array
     */
    protected function current(&$data, &$position)
    {
        $dataPart=current($data);
        $positionPart=current($position);
        $dataKey=key($data);
        $positionKey=key($position);
        if($lineNumber=$this->lineNumber($positionKey)){
            if($lineNumber>$this->linesToReport) {
                $progress = $lineNumber;
                $this->sendStatus( Status::WORKING, $progress );
                $this->linesToReport+=10;
            }
        }
        return [$dataPart, $positionPart, $dataKey, $positionKey];
    }

    /**
     * @param $data
     * @param $position
     * @return array
     */
    protected function next(&$data, &$position)
    {
        $dataPart=next($data);
        $positionPart=next($position);
        $dataKey=key($data);
        $positionKey=key($position);
        if($lineNumber=$this->lineNumber($positionKey)){
            if($lineNumber>$this->linesToReport) {
                $progress = $lineNumber;
                $this->sendStatus( Status::WORKING, $progress );
                $this->linesToReport=$lineNumber;
            }
        }
        return [$dataPart, $positionPart, $dataKey, $positionKey];
    }


    /**
     * @param int $status
     * @param int $progress
     */
    protected function sendStatus(int $status, int $progress)
    {
        $obj=$this->getStatusObject($status, $progress);
        $event = new StatusEvent($obj);
        if(isset($this->eventDispatcher)){
            $this->eventDispatcher->dispatch('status.update',$event);
        }
    }



    protected function getStatusObject(int $status, int $progress=0)
    {
        switch($status){
            case Status::COMMENCE:
                $this->startTime=new \DateTime('now');
                return new Status(Status::COMMENCE, $progress);
            case Status::WORKING:
                return new Status(Status::WORKING, $progress);
            case Status::COMPLETE:
                return new Status(Status::COMPLETE, 100);
            default:
                return null;
        }
    }

}