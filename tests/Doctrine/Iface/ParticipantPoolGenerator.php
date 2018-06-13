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


use App\Entity\Competition\Iface;
use App\Entity\Competition\Model;
use App\Entity\Models\Value;
use App\Entity\Sales\Iface\Participant;
use App\Entity\Sales\Tag;
use App\Exceptions\GeneralException;
use App\Exceptions\ParticipantCheckException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\IfaceRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\TagRepository;


class ParticipantPoolGenerator extends BaseParser
{

    /*
     * $participants[genre][proficiency][age][tpe][sex]=Participant;
     */
    private $participants = [];

    private $mappings;

    /**
     * @var IfaceRepository
     */
    private $ifaceRepository;
    /**
     * @var FormRepository
     */
    private $formRepository;


    /** @var Tag */
    private $participantTag;


    public function __construct(CompetitionRepository $competitionRepository,
                                ModelRepository $modelRepository,
                                IfaceRepository $ifaceRepository,
                                ValueRepository $valueRepository,
                                FormRepository $formRepository,
                                TagRepository $tagRepository)
    {
        parent::__construct( $competitionRepository,
                             $modelRepository,
                             $valueRepository );
        //$this->domainValueHash = $valueRepository->fetchDomainValueHash();
        //$this->valueById = $valueRepository->fetchAllValuesById();
        $this->ifaceRepository = $ifaceRepository;
        $this->formRepository = $formRepository;
        $this->participantTag = $tagRepository->fetch('participant');

    }

    /**
     * @param string $yaml
     * @return array
     * @throws GeneralException
     * @throws ParticipantCheckException
     */
    public function parse(string $yaml)
    {
        $r = $this->fetchPhpArray($yaml);
        if(key($r['data'])=='comment'){
            next($r['data']); next($r['position']);
        }
        list($competitionName, $competitionNamePosition, $competitionKey, $competitionKeyPosition)
            = $this->current($r['data'],$r['position']);
        $competition=$this->fetchCompetition($competitionName, $competitionNamePosition,
                                            $competitionKey, $competitionKeyPosition);
        /** @var Iface $iface */
        $iface=$this->ifaceRepository->findOneBy(['competition'=>$competition]);
        $this->mappings = $iface->getMapping();
        list($modelNames,$modelNamesPosition,$modelsKey,$modelsKeyPosition)
            = $this->next($r['data'],$r['position']);
        $this->fetchModels($modelNames,$modelNamesPosition,$modelsKey,$modelsKeyPosition);
        list($participantPool,$participantPoolPositions,$participantPoolKey,$participantPoolKeyPosition)
            = $this->next($r['data'],$r['position']);
        $this->buildParticipantPool($participantPool,$participantPoolPositions,$participantPoolKey, $participantPoolKeyPosition);
        return $this->participants;
    }

    /**
     * @param array $data
     * @param array $positions
     * @param string $key
     * @param string $keyPosition
     * @throws GeneralException
     * @throws ParticipantCheckException
     */
    private function buildParticipantPool(array $data,array $positions,string $key,string $keyPosition)
    {
        if($key!='participant-pool') {
            throw new GeneralException($key, $keyPosition, 'expected "participant-pool"',
                ParticipantExceptionCode::PARTICIPANT_POOL);
        }
        list($batch,$batchPosition, , ) = $this->current($data,$positions);
        while($batch) {
            $this->buildParticipantBatch($batch,$batchPosition);
            list($batch,$batchPosition, , ) = $this->next($data,$positions);
        }
    }

    /**
     * @param $data
     * @param $position
     * @throws GeneralException
     * @throws ParticipantCheckException
     */
    private function buildParticipantBatch($data,$position)
    {
       $component = [];
       $componentPosition = [];
       list($dataPart,$positionPart,$dataKey,$positionKey) = $this->current($data,$position);
       while($dataPart) {
           $acceptedKeys = ['genres','proficiencies','ages','sex', "type"];
           if(!in_array($dataKey,$acceptedKeys)){
               $expected = join('","',$acceptedKeys);
               throw new GeneralException($dataKey,$positionKey,"expected \"$expected\"",
                   ParticipantExceptionCode::INVALID_KEY);
           }
           $component[$dataKey]=$dataPart;
           $componentPosition[$dataKey]=$positionPart;
           list($dataPart,$positionPart,$dataKey,$positionKey) = $this->next($data,$position);
       }
       $this->layerGenre($component,$componentPosition);
    }

    /**
     * @param $component
     * @param $componentPosition
     * @throws GeneralException
     * @throws ParticipantCheckException
     */
    private function layerGenre($component, $componentPosition)
    {
        $positionGenre = current($componentPosition['genres']);
        foreach($component['genres'] as $genre) {
            if(!$this->hasDomainValue('style',$genre) && !$this->hasDomainValue('substyle',$genre)){
                throw new GeneralException($genre,$positionGenre, "is invalid",
                    ParticipantExceptionCode::INVALID_GENRE);
            }
            if(!isset($this->participants[$genre])){
                $this->participants[$genre]=[];
            }
            $this->layerProficiency($component, $componentPosition, $genre);
            $positionGenre = next($componentPosition['genres']);
        }
    }

    /**
     * @param $component
     * @param $componentPosition
     * @param string $genre
     * @throws GeneralException
     * @throws ParticipantCheckException
     */
    private function layerProficiency($component,$componentPosition, string $genre)
    {
        $positionProficiency = current($componentPosition['proficiencies']);
        foreach($component['proficiencies'] as $proficiency) {
            if(!$this->hasDomainValue('proficiency',$proficiency)) {
                throw new GeneralException($proficiency,$positionProficiency, "is invalid",
                    ParticipantExceptionCode::INVALID_PROFICIENCY);
            }
            if(!isset($this->participants[$genre][$proficiency])) {
                $this->participants[$genre][$proficiency]=[];
            }
            $this->layerAge($component,$componentPosition, $genre, $proficiency);
            $positionProficiency = next($componentPosition['proficiencies']);
        }
    }

    /**
     * @param int $proficiencyId
     * @return Value
     * @throws \Exception
     */

    private function classifyTypeA(int $proficiencyId):Value
    {
        /** @var Value $value */
        $value = $this->valueById[$proficiencyId];
        if($value->getDomain()->getName()!='proficiency'){
            throw new \Exception("$proficiencyId does not correspond to an proficiency.",9000);
        }
        switch($value->getName()){
            case 'Social':
            case 'Newcomer':
            case 'Pre Bronze':
            case 'Intermediate Bronze':
            case 'Full Bronze':
            case 'Open Bronze':
            case 'Bronze':
            case 'Pre Silver':
            case 'Intermediate Silver':
            case 'Full Silver':
            case 'Open Silver':
            case 'Silver':
            case 'Pre Gold':
            case 'Intermediate Gold':
            case 'Full Gold':
            case 'Open Gold':
            case 'Gold':
            case 'Gold Star 1':
            case 'Novice':
            case 'Gold Star 2':
            case 'Pre Championship':
            case 'Championship':
                return $this->domainValueHash['type']['Amateur'];
            case 'Rising Star-Pro':
            case 'Professional':
                return $this->domainValueHash['type']['Professional'];
            default:
                throw new \Exception("$proficiencyId was not found from available list of proficiencies.",9000);
        }
    }

    /**
     * @param int $proficiencyId
     * @return Value
     * @throws \Exception
     */
    private function classifyTypeB(int $proficiencyId):Value
    {
        /** @var Value $value */
        $value = $this->valueById[$proficiencyId];
        if($value->getDomain()->getName()!='proficiency'){
            throw new \Exception( "$proficiencyId does not correspond to a proficiency.", 9000 );
        }
        switch($value->getName()){
            case 'Social':
            case 'Newcomer':
            case 'Pre Bronze':
            case 'Intermediate Bronze':
            case 'Full Bronze':
            case 'Open Bronze':
            case 'Bronze':
            case 'Pre Silver':
            case 'Intermediate Silver':
            case 'Full Silver':
            case 'Open Silver':
            case 'Silver':
            case 'Pre Gold':
            case 'Intermediate Gold':
            case 'Full Gold':
            case 'Open Gold':
            case 'Gold':
            case 'Gold Star 1':
            case 'Novice':
            case 'Gold Star 2':
            case 'Pre Championship':
            case 'Championship':
                return $this->domainValueHash['type']['Student'];
            case 'Rising Star-Pro':
            case 'Professional':
                return $this->domainValueHash['type']['Teacher'];
            default:
                throw new \Exception("$proficiencyId was not found from available list.",9000);
        }
    }


    /**
     * @param $component
     * @param $componentPosition
     * @param string $genre
     * @param string $proficiency
     * @throws GeneralException
     * @throws ParticipantCheckException
     */
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
               ParticipantExceptionCode::INVALID_RANGE);
       }
       list($low,$high) = explode('-',$ages);
       if(!(is_numeric($low) && is_numeric($high))) {
           throw new GeneralException($ages, $agePosition, "invalid age range",
                    ParticipantExceptionCode::INVALID_RANGE);
       }
       $nlow = intval($low); $nhigh = intval($high);
       if (!(is_int($nlow) && is_int($nhigh) && ($nlow<=$nhigh))) {
          throw new GeneralException($ages, $agePosition, "invalid age range",
              ParticipantExceptionCode::INVALID_RANGE);
       }
       foreach($sex as $idx=>$s){
          if(!in_array($s,['M','F'])) {
              $position = $sexPosition[$idx];
              throw new GeneralException($s,$position, "expected M and/or F",
                  ParticipantExceptionCode::INVALID_SEX);
          }
       }
       if(!$this->hasDomainValue('type',$type)) {
           throw new GeneralException($type, $typePosition, "is invalid",
                        ParticipantExceptionCode::INVALID_TYPE);
       }

       for($nage=$nlow;$nage<=$nhigh;$nage++){
           if(!isset($this->participants[$genre][$proficiency][$nage])) {
               $this->participants[$genre][$proficiency][$nage]=[];
           }
           foreach($sex as $s){
               if(!isset($this->participants[$genre][$proficiency][$nage][$s])){
                   $this->participants[$genre][$proficiency][$nage][$s]=[];
               }
               /** @var string $type */
               if(!isset($this->participants[$genre][$proficiency][$nage][$s][$type])){
                   /** @var Participant $participant*/
                   $participant = new Participant(  $this->valueById,
                                                    $this->modelRepository->getModelById(),
                                                    $this->formRepository,
                                                    $this->participantTag);
                   $first = $genre.'-'.$proficiency.'-'.$nage;
                   $last  = $s.'-'.$type;
                   $proficiencyValue = $this->getDomainValue('proficiency',$proficiency);
                   $genreValue = $this->hasDomainValue('style',$genre)?
                                            $this->getDomainValue('style',$genre):
                                            $this->getDomainValue('substyle', $genre);
                   /** @var int $proficiencyId */
                   $proficiencyId = $proficiencyValue->getId();
                   $participant->setFirst($first)
                       ->setLast($last)
                       ->setSex($s)
                       ->setYears($nage)
                       ->addGenreProficiency($genreValue->getId(),$proficiencyId);
                   $models=$this->fetchModels();
                   /** @var Model $model */
                   foreach($models as $model){
                       $participant->addModel($model->getId(),$model);
                   }
                   /** @var Value $typeA */
                   $typeA=$this->classifyTypeA($proficiencyId);
                   $participant->setTypeA($typeA->getId());
                   /** @var Value $typeB */
                   $typeB=$this->classifyTypeB($proficiencyId);
                   $participant->setTypeB($typeB->getId());
                   $this->participants[$genre][$proficiency][$nage][$s][$type]=$participant;
               }
           }
       }
    }
}