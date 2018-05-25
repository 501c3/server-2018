<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/23/18
 * Time: 10:59 AM
 */

namespace App\Tests\Doctrine\Iface;


use App\Exceptions\GeneralException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;

class ParticipantPoolGenerator extends BaseParser
{

    /*
     * $participants[genre][proficiency][age][type][sex]=Participant;
     */
    private $participants = [];

    private $genresValues = [];

    public function __construct(CompetitionRepository $competitionRepository,
                                ModelRepository $modelRepository,
                                ValueRepository $valueRepository)
    {
        parent::__construct( $competitionRepository,
                             $modelRepository,
                             $valueRepository );
    }

    public function parse(string $yaml)
    {
        $r = $this->fetchPhpArray($yaml);
        if(key($r['data'])=='comment'){
            next($r['data']); next($r['position']);
        }
        list($competitionName, $competitionNamePosition, $competitionKey, $competitionKeyPosition)
            = $this->current($r['data'],$r['position']);
        $this->fetchCompetition($competitionName, $competitionNamePosition,
                                $competitionKey, $competitionKeyPosition);
        list($modelNames,$modelNamesPosition,$modelsKey,$modelsKeyPosition)
            = $this->next($r['data'],$r['position']);
        $this->fetchModels($modelNames,$modelNamesPosition,$modelsKey,$modelsKeyPosition);
        list($participantPool,$participantPoolPositions,$participantPoolKey,$participantPoolKeyPosition)
            = $this->next($r['data'],$r['position']);
        $this->buildParticipantPool($participantPool,$participantPoolPositions,$participantPoolKey, $participantPoolKeyPosition);
    }

    private function buildParticipantPool(array $data,array $positions,string $key,string $keyPosition)
    {
        if($key!='participant-pool') {
            throw new GeneralException($key, $keyPosition, 'expected "participant-pool"',
                ExceptionCode::PARTICIPANT_POOL);
        }
        list($batch,$batchPosition, , ) = $this->current($data,$positions);
        while($batch) {
            $this->buildParticipantBatch($batch,$batchPosition);
            list($batch,$batchPosition, , ) = $this->next($data,$positions);
        }
    }

    private function buildParticipantBatch($data,$position)
    {
       $component = [];
       $componentPosition = [];
       list($dataPart,$positionPart,$dataKey,$positionKey) = $this->current($data,$position);
       while($dataPart) {
           $acceptedKeys = ['genres','proficiencies','ages','sex','type'];
           if(!in_array($dataKey,$acceptedKeys)){
               $expected = join('","',$acceptedKeys);
               throw new GeneralException($dataKey,$positionKey,"expected \"$expected\"",
                   ExceptionCode::INVALID_KEY);
           }
           $component[$dataKey]=$dataPart;
           $componentPosition[$dataKey]=$positionPart;
           list($dataPart,$positionPart,$dataKey,$positionKey) = $this->next($data,$position);
       }
       $this->layerGenre($component,$componentPosition);
    }

    private function layerGenre($component, $componentPosition)
    {
        $positionGenre = current($componentPosition['genres']);
        foreach($component['genres'] as $genre) {
            if(!$this->hasDomainValue('style',$genre) && !$this->hasDomainValue('substyle',$genre)){
                throw new GeneralException($genre,$positionGenre, "is invalid",
                    ExceptionCode::INVALID_GENRE);
            }
            if(!isset($this->participants[$genre])){
                $this->participants[$genre]=[];
            }
            $this->layerProficiency($component, $componentPosition, $genre);
            $positionGenre = next($componentPosition['genres']);
        }
    }

    private function layerProficiency($component,$componentPosition, string $genre)
    {
        $positionProficiency = current($componentPosition['proficiencies']);
        foreach($component['proficiencies'] as $proficiency) {
            if(!$this->hasDomainValue('proficiency',$proficiency)) {
                throw new GeneralException($genre,$positionProficiency, "is invalid",
                    ExceptionCode::INVALID_PROFICIENCY);
            }
            if(!isset($this->participants[$genre][$proficiency])) {
                $this->participants[$genre][$proficiency]=[];
            }
            $this->layerAge($component,$componentPosition, $genre, $proficiency);
            $positionProficiency = next($componentPosition['proficiencies']);
        }
    }

    private function layerAge($component, $componentPosition, string $genre, string $proficiency)
    {
       $ages = $component['ages'];
       $agePosition = $componentPosition['ages'];
       $sex = $component['sex'];
       $sexPosition = $componentPosition['sex'];
       $type = $component['type'];
       $typePosition = $componentPosition['type'];
       if(strpos($ages,'-')==0) {
           throw new GeneralException($ages, $agePosition, "invalid age range",
               ExceptionCode::INVALID_RANGE);
       }
       list($low,$high) = explode('-',$ages);
       if(!(is_numeric($low) && is_numeric($high))) {
           throw new GeneralException($ages, $agePosition, "invalid age range",
                    ExceptionCode::INVALID_RANGE);
       }
       $nlow = intval($low); $nhigh = intval($high);
       if (!(is_int($nlow) && is_int($nhigh) && ($nlow<=$nhigh))) {
          throw new GeneralException($ages, $agePosition, "invalid age range",
              ExceptionCode::INVALID_RANGE);
       }
       foreach($sex as $idx=>$s){
          if(!in_array($s,['M','F'])) {
              $position = $sexPosition[$idx];
              throw new GeneralException($s,$position, "expected M and/or F",
                  ExceptionCode::INVALID_SEX);
          }
       }
       if(!$this->hasDomainValue('type',$type)) {
           throw new GeneralException($type, $typePosition, "is invalid",
                        ExceptionCode::INVALID_TYPE);
       }

       for($nage=$nlow;$nage<=$nhigh;$nage++){
           if(!isset($this->participants[$genre][$proficiency][$nage])) {
               $this->participants[$genre][$proficiency][$nage]=[];
           }
           foreach($sex as $s){
               if(!isset($this->participants[$genre][$proficiency][$nage][$s])){
                   $this->participants[$genre][$proficiency][$nage][$s]=[];
               }
               if(!isset($this->participants[$genre][$proficiency][$nage][$s])){
                   $this->participants[$genre][$proficiency][$nage][$s][$type]=null;
                   //TODO: Create Participant
               }
           }
       }



    }

}