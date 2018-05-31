<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/23/18
 * Time: 9:05 AM
 */

namespace App\Tests\Doctrine\Iface;


use App\Exceptions\GeneralException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;

class PlayerPoolGenerator extends BaseParser
{
    private $competition;

    private $models;

    private $domainValueHash;

    private $participantPool;

    private $coupling = [];

    public function __construct(
        CompetitionRepository $competitionRepository,
        ModelRepository $modelRepository,
        ValueRepository $valueRepository
    )
    {
        parent::__construct($competitionRepository,
                            $modelRepository,
                            $valueRepository);
        $this->domainValueHash=$valueRepository->fetchDomainValueHash();
    }

    public function setParticipantPool(array $participantPool)
    {
        $this->participantPool = $participantPool;
        return $this;
    }

    public function parse(string $yaml)
    {
        $r = $this->fetchPhpArray($yaml);
        if(key($r['data'])=='comment'){
            next($r['data']); next($r['position']);
        }
        list($competitionName, $competitionNamePosition, $competitionKey, $competitionKeyPosition)
            = $this->current($r['data'],$r['position']);
        $this->competition=$this->fetchCompetition($competitionName, $competitionNamePosition,
                                                    $competitionKey, $competitionKeyPosition);
        $this->models=$this->fetchModels(next($r['data']), next($r['position']),
                                         key($r['data']), key($r['position']));
        $genres=$this->fetchGenres(next($r['data']), next($r['position']),
                                        key($r['data']), key($r['position']));

        $this->buildCouples($genres, next($r['data']),next($r['position']),
                            key($r['data']),key($r['position']));
        $this->buildSolos($genres, next($r['data']),next($r['position']),
                                key($r['data']),key($r['position']));
    }


    public function fetchGenres($data,$position,$key,$keyPosition)
    {
        if($key!='genres') {
            throw new GeneralException($key,$keyPosition,"expected \"genres\"",
                                        PlayerExceptionCode::GENRES);
        }
        $genrePosition = current($position);
        foreach($data as $genre){
            if(!isset($this->domainValueHash['style'][$genre])&&!isset($this->domainValueHash['substyle'][$genre])){
                throw new GeneralException($genre,$genrePosition,'is invalid',
                                            PlayerExceptionCode::INVALID_GENRE);
            }
            $genrePosition=next($position);
        }
        return $data;
    }



    public function buildCouples(array $genres, array $data,array $position,string $key, string $keyPosition) {
        if($key!='couples') {
            throw new GeneralException($key,$keyPosition, "expected \"couples\"" ,
                PlayerExceptionCode::COUPLES_SOLO);
        }
        list($dataPart,$positionPart, , ) = $this->current($data,$position);
        while($dataPart) {
            foreach($genres as $genre) {
                $this->buildPlayerCoupling($genre, $dataPart, $positionPart);
            }
            list($dataPart, $positionPart, , ) = $this->next($data, $position);
        }
    }

    public function buildSolos(array $genres, array $data, array $position, $key, $keyPosition)
    {
        if($key!='solo') {
            throw new GeneralException($key,$keyPosition, "expected \"couples\"" ,
                PlayerExceptionCode::COUPLES_SOLO);
        }
        list($dataPart,$positionPart, , ) = $this->current($data,$position);
        while($dataPart) {
            var_dump($dataPart);
            list($dataPart, $positionPart, , ) = $this->next($data, $position);
        }
    }

    public function buildPlayerCoupling(string $genre, $dataPart, $positionPart)
    {

       list($leadData,$leadPosition,$leadKey,$leadKeyPosition)
           = $this->current($dataPart,$positionPart);
       if($leadKey!='lead') {
           throw new GeneralException($leadKey, $leadKeyPosition, "expected \"lead\"",
               PlayerExceptionCode::LEAD);
       }
       $leader = $this->fetchLeader($genre,$leadData,$leadPosition);
       list($followData,$followDataPosition,$followKey,$followKeyPosition)
           = $this->next($dataPart,$positionPart);
       if($followKey!='follow') {
           throw new GeneralException($followKey,$followKeyPosition,"expected \"follow\"",
               PlayerExceptionCode::FOLLOW);
       }
       list($followParticipant,$followParticipantPosition, , )
           = $this->current($followData,$followDataPosition);
       while($followParticipant) {
           $result = $this->fetchFollowerExpected($genre, $followParticipant, $followParticipantPosition);
           array_push($this->coupling, [ 'leader'=>$leader,
                                                'follower'=>$result['participant'],
                                                'expected'=>$result['expected']]);
           list($followParticipant, $followParticipantPosition, , )
               =$this->next($followData, $followDataPosition);
       }

    }


    private function fetchParticipant($genre, $data, $position)
    {
        $expectedKeys = ['proficiency','age','sex','type','expected'];
        $expectedKeysStr = join('","', $expectedKeys);
        $foundKeys = [];
        list($dataPart,$positionPart,$key, $keyPosition)
            = $this->current($data,$position);
        $initialPosition = $keyPosition;
        $loc = ['position'=>$keyPosition, 'genre'=>$genre];
        while($dataPart) {
            if(!in_array($key, $expectedKeys)) {
                throw new GeneralException($key, $keyPosition, "expected \"$expectedKeysStr\"",
                    PlayerExceptionCode::KEYS);
            }
            array_push($foundKeys, $key);
            switch($key) {
                case 'proficiency':
                    if(!isset($this->domainValueHash['proficiency'][$dataPart])) {
                        throw new GeneralException($dataPart, $positionPart, "invalid proficiency",
                            PlayerExceptionCode::INVALID_PROFICIENCY);
                    }
                    $loc['proficiency']=$dataPart;
                    break;
                case 'age':
                    if(!is_numeric($dataPart)) {
                        throw new GeneralException($dataPart,$positionPart, "expected number",
                                PlayerExceptionCode::INVALID_AGE);
                    }
                    $loc['age']=$dataPart;
                    break;
                case 'sex':
                    if(!in_array($dataPart, ['M','F'])) {
                        throw new GeneralException($dataPart,$positionPart, "expected \"M\" or \"F\"",
                                PlayerExceptionCode::INVALID_SEX);
                    }
                    $loc['sex']=$dataPart;
                    break;
                case 'type':
                    if(!isset($this->domainValueHash['type'][$dataPart])) {
                        throw new GeneralException($dataPart, $positionPart, "invalid type",
                                    PlayerExceptionCode::INVALID_TYPE);
                    }
                    $loc['type']=$dataPart;
                    break;
                case 'expected':
                    $loc['expected'] = $this->fetchExpected($genre, $dataPart,$positionPart);
            }
            list($dataPart,$positionPart, $key, $keyPosition)
                = $this->next($data,$position);
        }
        $missingKeys = array_diff(['proficiency','age','sex','type'], $foundKeys);
        if(count($missingKeys)) {
            throw new GeneralException('Missing keys', $initialPosition, join(',',$missingKeys),
                                        PlayerExceptionCode::MISSING_KEYS);
        }
        return $loc;
    }

    private function fetchLeader($genre, $data, $position)
    {
        $loc=$this->fetchParticipant($genre, $data, $position);
        $g = $loc['genre'];
        $p = $loc['proficiency'];
        $a = $loc['age'];
        $s = $loc['sex'];
        $t = $loc['type'];
        if(!isset($this->participantPool[$g][$p][$a][$s][$t])) {
            $participant = "$g:$p:$a:$s:$t";
            throw new GeneralException($participant,$loc['position'],"is not defined",
                PlayerExceptionCode::UNDEFINED);
        }
        return $this->participantPool[$g][$p][$a][$s][$t];

    }


    private function fetchFollowerExpected($genre, $data, $position)
    {
        $loc=$this->fetchParticipant($genre, $data, $position);
        $g = $loc['genre'];
        $p = $loc['proficiency'];
        $a = $loc['age'];
        $s = $loc['sex'];
        $t = $loc['type'];
        $e = $loc['expected'];
        $position = $loc['position'];
        if(!isset($this->participantPool[$g][$p][$a][$s][$t])) {
            $participant = "$g:$p:$a:$s:$t";
            throw new GeneralException($participant,$position,"is not defined",
                PlayerExceptionCode::UNDEFINED);
        }
        return ['participant'=>$this->participantPool[$g][$p][$a][$s][$t], 'expected'=>$loc['expected']];
    }

    private function fetchExpected($genre, $data, $position)
    {
        list($dataPart,$positionPart, $key, $keyPosition)
            = $this->current($data,$position);
        $keys = ['proficiency','age','type'];
        $keyStr = join('","',$keys);
        $expected = [];
        $expected['genre']=isset($this->domainValueHash['style'])?
                                    $this->domainValueHash['style']:
                                    $this->domainValueHash['substyle'];
        while($dataPart) {
            if(!in_array($key,$keys)) {
                throw new GeneralException($key,$keyPosition,"expected \"$keyStr\"",
                                PlayerExceptionCode::EXPECTED_KEYS);
            }
            switch($key) {
                case 'proficiency':
                    if(!isset($this->domainValueHash['proficiency'][$dataPart])) {
                        throw new GeneralException($dataPart,$positionPart,'is invalid proficiency',
                                    PlayerExceptionCode::INVALID_EXPECTED_PROFICIENCY);
                    }
                    $expected['age']=$this->domainValueHash['proficiency'][$dataPart];
                    break;
                case 'age':
                    if(!isset($this->domainValueHash['age'][$dataPart])) {
                        throw new GeneralException($dataPart,$positionPart, 'is invalid age',
                                        PlayerExceptionCode::INVALID_EXPECTED_AGE);
                    }
                    $expected['age']=$this->domainValueHash['age'][$dataPart];
                    break;
                case 'type':
                    if(!isset($this->domainValueHash['type'][$dataPart])) {
                        throw new GeneralException($dataPart,$positionPart, 'is invalid type',
                            PlayerExceptionCode::INVALID_EXPECTED_TYPE);
                    }
                    $expected['type']=$this->domainValueHash['type'][$dataPart];
            }
            list($dataPart,$positionPart, $key, $keyPosition )
                = $this->next($data,$position);
        }
        return $expected;
    }
}