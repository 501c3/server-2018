<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/23/18
 * Time: 9:11 AM
 */

namespace App\Tests\Doctrine\Iface;

use App\Exceptions\GeneralException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;
use App\Subscriber\Status;
use App\Subscriber\StatusEvent;
use App\Utils\YamlPosition;

class BaseParser
{

    private $lineCount;

    private $competition;

    private $models;
    /**
     * @var CompetitionRepository
     */
    protected $competitionRepository;

    /**
     * @var ModelRepository
     */
    protected $modelRepository;

    /**
     * @var integer
     */
    private $linesToReport;

    protected $startTime;

    protected $domainValueHash = [];

    protected $valueById=[];

    /**
     * BaseParser constructor.
     * @param CompetitionRepository $competitionRepository
     * @param ModelRepository $modelRepository
     * @param ValueRepository $valueRepository
     */
    protected function __construct(
        CompetitionRepository $competitionRepository,
        ModelRepository $modelRepository,
        ValueRepository $valueRepository)
    {
        $this->competitionRepository = $competitionRepository;
        $this->modelRepository = $modelRepository;
        $values=$valueRepository->fetchAllDomainValues();
        /** @var Value $value */
        foreach($values as $value)
        {
            $domain = $value->getDomain();
            $domName = $domain->getName();
            if(!isset($this->domainValueHash[$domName])){
                $this->domainValueHash[$domName]=[];
            }
            $this->domainValueHash[$domName][$value->getName()]=$value;
            $this->valueById[$value->getId()]=$value;
        }
    }

    /**
     * @param string $domain
     * @param string $value
     * @return bool
     */
    protected function hasDomainValue(string $domain, string $value) {
        return isset($this->domainValueHash[$domain][$value]);
    }

    /**
     * @param string $domain
     * @param string $value
     * @return mixed
     */
    protected function getDomainValue(string $domain,string $value) {
        return $this->domainValueHash[$domain][$value];
    }

    /**
     * @param string $yaml
     * @return array
     * @throws \Exception
     */
    protected function fetchPhpArray(string $yaml)
    {
        $r=YamlPosition::parse($yaml);
        $this->lineCount = YamlPosition::getLineCount();
        return $r;
    }

    /**
     * @param mixed ...$params
     * @return null|object
     * @throws GeneralException
     */
    protected function fetchCompetition(...$params)
    {
        list($name, $namePosition, $key, $keyPosition) = $params;
        if($key != 'competition') {
            throw new GeneralException($key, $keyPosition, 'expected "competition"',
                ParticipantExceptionCode::COMPETITION);
        }
        $competition=$this->competitionRepository->findOneBy(['name'=>$name]);
        if(!$competition){
            throw new GeneralException($name, $namePosition, "does not exist",
                ParticipantExceptionCode::INVALID_COMPETITION);
        }
        $this->competition = $competition;
        return $competition;
    }

    /**
     * @param mixed ...$params
     * @return array
     * @throws GeneralException
     */
    protected function fetchModels(...$params)
    {
        if(count($params)==0){
            return $this->models;
        }
        list($data,$positions,$key,$keyPosition) = $params;
        if($key != 'models') {
            throw new GeneralException($key, $keyPosition, 'expected "models"',
                ParticipantExceptionCode::MODELS);
        }
        $models = [];
        $position=current($positions);
        foreach($data as $modelName){
            $model=$this->modelRepository->findOneBy(['name'=> $modelName]);
            if(!$model){
                throw new GeneralException($modelName,$position,"is invalid",
                    ParticipantExceptionCode::INVALID_MODEL);
            }
            $models[$modelName] = $model;
            $position = next($positions);
        }
        $this->models = $models;
        return $models;
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
        }
        return null;
    }
}