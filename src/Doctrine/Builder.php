<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/23/18
 * Time: 11:11 AM
 */

namespace App\Doctrine;


use App\Subscriber\Status;
use App\Subscriber\StatusEvent;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Builder implements EventSubscriberInterface
{
    /** @var TraceableEventDispatcher*/
    protected $eventDispatcher;

    /** @var int */
    private $linesToReport = 0;

    /**  @var \DateTime */
    private $startTime;


    public function setDispatcher(TraceableEventDispatcher $dispatcher)
    {
        $this->eventDispatcher=$dispatcher;
    }

    /**
     * @param array $positions
     * @return string
     */
    protected function lineNumbers(array $positions){
        $pos=[];
        $lines = [];
        while($location=array_shift($positions)){
            preg_match('/R(?P<row>\d+)C(?P<col>\d+)/',$location, $pos);
            array_push($lines, $pos['row']);
        }
        return join(',',$lines);
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
        }
        return null;
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
        return [$dataPart, $positionPart, $dataKey, $positionKey];
    }

    public static function getSubscribedEvents()
    {
        // TODO: Implement getSubscribedEvents() method.
        return [];
    }


    public function prePersist(LifecycleEventArgs $args)
    {
    }


    public function preUpdate(LifecycleEventArgs $args)
    {
    }

}