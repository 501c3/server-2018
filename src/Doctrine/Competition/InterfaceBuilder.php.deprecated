<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/9/18
 * Time: 9:48 AM
 */

namespace App\Doctrine\Competition;


use App\Doctrine\Builder;
use App\Entity\Competition\Competition;
use App\Entity\Competition\Iface;
use App\Entity\Models\Model;
use App\Entity\Models\Value;
use App\Exceptions\GeneralException;
use App\Exceptions\InterfaceExceptionCode;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\IfaceRepository;
use App\Repository\Models\DomainRepository;
use App\Repository\Models\ModelRepository;
use App\Repository\Models\ValueRepository;
use App\Subscriber\Status;
use App\Utils\YamlPosition;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface;

class InterfaceBuilder extends Builder
{

    private $domainValues = [];

    private $modelId = [];

    private $mappingCheck;

    /**
     * @var ValueRepository
     */
    private $valueRepository;
    /**
     * @var CompetitionRepository
     */
    private $competitionRepository;
    /**
     * @var ModelRepository
     */
    private $modelRepository;
    /**
     * @var DomainRepository
     */
    private $domainRepository;
    /**
     * @var IfaceRepository
     */
    private $ifaceRepository;

    public function __construct(
        ModelRepository $modelRepository,
        DomainRepository $domainRepository,
        ValueRepository $valueRepository,
        CompetitionRepository $competitionRepository,
        IfaceRepository $ifaceRepository,
        TraceableEventDispatcherInterface $eventDispatcher = null
    )
    {
        $this->modelRepository = $modelRepository;
        $this->valueRepository = $valueRepository;
        $this->competitionRepository = $competitionRepository;
        $this->domainRepository = $domainRepository;
        $this->ifaceRepository = $ifaceRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $yamlText
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function build(string $yamlText)
    {
        $r = YamlPosition::parse( $yamlText );
        $lineCount = YamlPosition::getLineCount();
        $this->sendStatus( Status::COMMENCE, $lineCount );
        if (key( $r['data'] ) == 'comment') {
            next( $r['data'] );
            next( $r['position'] );
        }
        $competition = $this->findCompetition( current( $r['data'] ), current( $r['position'] ),
            key( $r['data'] ), key( $r['position'] ) );

        $this->loadModelDomainValues( next( $r['data'] ), next( $r['position'] ),
            key( $r['data'] ), key( $r['position'] ) );

        $setups=$this->buildSetups( next( $r['data'] ), next( $r['position'] ),
            key( $r['data'] ), key( $r['position'] ) );

        $this->mappingCheck = $setups['proficiency'];

        $mappings=$this->buildMappings( next( $r['data'] ), next( $r['position'] ),
            key( $r['data'] ), key( $r['position'] ) );

        $iface = new Iface();
        $iface->setName('participation')
            ->setCompetition($competition)
            ->setSetup($setups)
            ->setMapping($mappings);
        $em=$this->ifaceRepository->getEntityManager();
        $em->persist($iface);
        $em->flush();
        $this->sendStatus( Status::COMPLETE, 100 );
    }


    /**
     * @param string $dataPart
     * @param string $positionPart
     * @param string $dataKey
     * @param string $positionKey
     * @return Competition
     * @throws GeneralException
     */
    private function findCompetition(string $dataPart, string $positionPart, string $dataKey, string $positionKey):Competition
    {
        if ($dataKey != 'competition') {
            throw new GeneralException( $dataKey, $positionKey, "expected \"competition\"",
                InterfaceExceptionCode::COMPETITION );
        }
        /** @var Competition $competition */
        $competition = $this->competitionRepository->findOneBy( ['name' => $dataPart] );
        if (!$competition) {
            throw new GeneralException( $dataPart, $positionPart, "not found",
                InterfaceExceptionCode::INVALID_COMPETITION );
        }
        return $competition;
    }

    /**
     * @param $dataPart
     * @param $dataPosition
     * @param $dataKey
     * @param $positionKey
     * @throws GeneralException
     */
    private function loadModelDomainValues($dataPart, $dataPosition, $dataKey, $positionKey)
    {
        if ($dataKey != 'models') {
            throw new GeneralException( $dataKey, $positionKey, "expected \"models\"",
                InterfaceExceptionCode::MODELS );
        }
        list( $modelName, $position, , ) = $this->current( $dataPart, $dataPosition );
        while ($modelName && $position) {
            /** @var Model $model */
            $model = $this->modelRepository->findOneby( ['name' => $modelName] );
            if (!$model) {
                throw new GeneralException( $modelName, $position, "is an invalid model",
                    InterfaceExceptionCode::INVALID_MODEL );
            }
            $this->loadDomainValues( $model );
            list( $modelName, $position, , ) = $this->next( $dataPart, $dataPosition );
        }
    }

    /**
     * @param Model $model
     */
    private function loadDomainValues(Model $model)
    {
        $modelName = $model->getName();
        if (!isset( $this->competitionModel[$modelName] )) {
            $this->modelId[$modelName] = $model->getId();
        }
        $domainValues = $this->valueRepository->fetchDomainValues( $model );
        foreach ($domainValues as $value) {
            /** @var Value $value */
            $domainName = $value->getDomain()->getName();
            if (!isset( $this->domainValues[$domainName] )) {
                $this->domainValues[$domainName] = [];
            }
            $this->domainValues[$domainName][$value->getName()] = $value;
        }
    }

    /**
     * @param array $dataPart
     * @param array $positionPart
     * @param string $dataKey
     * @param string $positionKey
     * @return array
     * @throws GeneralException
     */
    private function buildSetups(array $dataPart, array $positionPart, string $dataKey, string $positionKey)
    {
        if ($dataKey != 'setups') {
            throw new GeneralException( $dataKey, $positionKey, "expected \"setups\"",
                InterfaceExceptionCode::SETUPS );
        }
        list( $participantsPart, $participantsPosition, $participantsKey, $participantsPositionKey )
            = $this->current( $dataPart, $positionPart );
        if ($participantsKey != 'participant') {
            throw new GeneralException( $participantsKey, $participantsPositionKey, "expected \"participants\"",
                InterfaceExceptionCode::PARTICIPANT );
        }
        $participantsSetup=$this->buildParticipantsSetup( $participantsPart, $participantsPosition );

        return $participantsSetup;
    }


    /**
     * @param array $data
     * @param array $positions
     * @return array
     * @throws GeneralException
     */
    private function buildParticipantsSetup(array $data, array $positions){
        list($typePart, $typePartPosition, $typeKey, $typeKeyPosition) = $this->current($data, $positions);
        if($typeKey != 'type') {
            throw new GeneralException($typeKey, $typeKeyPosition, "expected \"type\"",
                InterfaceExceptionCode::TYPE);
        }
        $typeSetup=[];
        list($type, $typePosition, , ) = $this->current($typePart, $typePartPosition);
        while($type && $typePosition) {
            $typeValue=$this->getPrimitive('type',$type);
            if(!$typeValue) {
                throw new GeneralException($type,$typePosition,'is invalid',
                    InterfaceExceptionCode::INVALID_TYPE);
            }
            $typeSetup[$typeValue->getName()]=$typeValue->getId();
            list($type, $typePosition, , ) = $this->next($typePart, $typePartPosition);
        }

        list($proficiencyPart, $proficiencyPartPosition, $proficiencyKey, $proficiencyKeyPosition)
            = $this->next($data, $positions);
        if($proficiencyKey != 'proficiency') {
            throw new GeneralException( $proficiencyKey, $proficiencyKeyPosition, "expected \"proficiency\"",
                InterfaceExceptionCode::PROFICIENCY);
        }
        $proficiencySelections = $this->buildProficiencySelections($proficiencyPart, $proficiencyPartPosition);
        list($combinationPart, $combinationPartPosition, $combinationKey, $combinationKeyPosition)
            = $this->next($data,$positions);
        if($combinationKey!='combinations') {
            throw new GeneralException($combinationKey, $combinationKeyPosition,
                'expected proficiency-combinations',
                InterfaceExceptionCode::COMBINATIONS);
        }
        $combinations = $this->checkCombinations($combinationPart,$combinationPartPosition);
        return ['type'=>$typeSetup, 'proficiency'=> $proficiencySelections, 'combination'=>$combinations];
    }

    /**
     * @param array $data
     * @param array $position
     * @return array
     * @throws GeneralException
     *
     * Example $proficiencySelections['Amateur']['Bronze']= n  where n is the numeric id for bronze
     * Information passed to client as JSON
     */
    private function buildProficiencySelections(array $data, array $position)
    {
        $proficiencySelections=[];
        list($proficiencyPart, $proficiencyPartPosition, $typeKey, $typeKeyPosition)
            = $this->current($data, $position);
        while($proficiencyPart && $proficiencyPartPosition){
            if(is_null($this->getPrimitive('type',$typeKey))) {
                throw new GeneralException($typeKey, $typeKeyPosition, "is invalid",
                    InterfaceExceptionCode::PROFICIENCY_TYPE);
            }
            if(!isset($proficiencySelections[$typeKey])){
                $proficiencySelections[$typeKey]=[];
            }
            list($proficiencyName, $proficiencyNamePosition, , )
                =$this->current($proficiencyPart, $proficiencyPartPosition);
            while($proficiencyName && $proficiencyNamePosition){
                $proficiencyValue = $this->getPrimitive('proficiency', $proficiencyName);
                if(!isset($proficiencyValue)){
                    throw new GeneralException($proficiencyName, $proficiencyNamePosition, 'is invalid',
                        InterfaceExceptionCode::INVALID_PROFICIENCY);
                }
                $proficiencySelections[$typeKey][$proficiencyValue->getName()]=$proficiencyValue->getId();
                list($proficiencyName, $proficiencyNamePosition, , )
                    =$this->next($proficiencyPart, $proficiencyPartPosition);
            }
            list($proficiencyPart, $proficiencyPartPosition, $typeKey, $typeKeyPosition)
                = $this->next($data, $position);
        }
        return $proficiencySelections;
    }

    /**
     * @param array $data
     * @param array $positions
     * @return array
     * @throws GeneralException
     */
    private function checkCombinations(array $data, array $positions){
       list($combination,$combinationPosition,$key,$keyPosition)
           = $this->current($data,$positions);
       while($combination && $combinationPosition){
           if(is_null($this->getPrimitive('type',$key))){
               throw new GeneralException($key,$keyPosition,"is invalid",
                   InterfaceExceptionCode::INVALID_TYPE);
           }
           list($secondary, $secondaryPosition, $secondaryKey, $secondaryKeyPosition)
               = $this->current($combination,$combinationPosition);
           while($secondary && $secondaryPosition){

               if(is_null($this->getPrimitive('type',$secondaryKey))){
                   throw new GeneralException($secondaryKey,$secondaryKeyPosition,"is invalid",
                       InterfaceExceptionCode::INVALID_COMBINATION);
               }
               if(is_null($this->getPrimitive('type',$secondary))){
                   throw new GeneralException($secondary,$secondaryPosition, "is invalid",
                       InterfaceExceptionCode::INVALID_COMBINATION);
               }
               list($secondary,$secondaryPosition, , )=$this->next($combination,$combinationPosition);
           }
           list($combination,$combinationPosition,$key,$keyPosition)
               = $this->next($data,$positions);
       }
       return $data;
    }


    /**
     * @param string $domainName
     * @param string $key
     * @return Value|null
     */
    private function getPrimitive(string $domainName, string $key):?Value {
        if (isset($this->domainValues[$domainName][$key])) {
            return $this->domainValues[$domainName][$key];
        }
        $domain = $this->domainRepository->findOneBy(['name'=>$domainName]);
        /** @var Value|null $result */
        $result = $this->valueRepository->findOneBy(['name'=>$key, 'domain'=>$domain]);
        if($result){
            $this->domainValues[$domainName][$key]=$result;
        }
        return $result;
    }

    /**
     * @param $dataPart
     * @param $positionPart
     * @param $dataKey
     * @param $positionKey
     * @return array
     * @throws GeneralException
     */
    private function buildMappings(array $dataPart, array $positionPart, string $dataKey, string $positionKey)
    {
        if($dataKey != 'mappings') {
            throw new GeneralException($dataKey, $positionKey, "expected \"mappings\"",
                    InterfaceExceptionCode::MAPPINGS);
        }

        list($genrePart,$genrePosition, $genreKey, $genreKeyPosition)
            = $this->current($dataPart, $positionPart);
        if($genreKey != 'genre'){
            throw new GeneralException($genreKey, $genreKeyPosition, "expected \"genre\"",
                InterfaceExceptionCode::GENRE);
        }
        $genreMapping = $this->buildGenreMapping($genrePart, $genrePosition);
        list($proficiencyPart,$proficiencyPosition,$proficiencyKey,$proficiencyKeyPosition)
            = $this->next($dataPart, $positionPart);
        if($proficiencyKey != 'proficiency'){
            throw new GeneralException($proficiencyKey, $proficiencyKeyPosition, "expected \"proficiency\"",
                InterfaceExceptionCode::MAPPING_PROFICIENCY);
        }
        $proficiencyIdMap = $this->buildProficiencyMapping($proficiencyPart,$proficiencyPosition);
        list($agePart,$agePosition, $ageKey, $ageKeyPosition)
            = $this->next($dataPart, $positionPart);
        if($ageKey != 'age') {
            throw new GeneralException($ageKey, $ageKeyPosition, "expected \"age\"",
                InterfaceExceptionCode::MAPPING_AGE);
        }
        $ageIdMap = $this->buildAgeMapping($agePart,$agePosition);

        return ['genre'=>$genreMapping, 'proficiency'=>$proficiencyIdMap, 'age'=>$ageIdMap];
    }

    /**
     * @param array $data
     * @param array $position
     * @return array
     * @throws GeneralException
     */
    private function buildGenreMapping(array $data, array $position):array
    {
        list($genreName, $genrePosition, , ) = $this->current($data,$position);

        $genreClassification = [];
        while($genreName && $genrePosition){
            if(isset($this->domainValues['style'][$genreName])){
                /** @var Value $value */
                $value = $this->domainValues['style'][$genreName];
                $genreClassification[$genreName]=['class'=>'style', 'id'=>$value->getId()];
            } elseif (isset($this->domainValues['substyle'][$genreName])) {
                /** @var Value $value */
                $value = $this->domainValues['substyle'][$genreName];
                $genreClassification[$genreName]=['class'=>'substyle', 'id'=>$value->getId()];
            } else {
                throw new GeneralException($genreName, $genrePosition, 'is invalid',
                    InterfaceExceptionCode::INVALID_GENRE);
            }
            list($genreName,$genrePosition, , ) = $this->next($data,$position);
        }
        return $genreClassification;
    }


    /**
     * @param array $data
     * @param array $position
     * @return array
     * @throws GeneralException
     */
    public function buildProficiencyMapping(array $data, array $position):array
    {
        $proficiencyIdMap = [];
        list($proficiencyEquivalents, $proficiencyEquivalentsPosition, $typeKey, $typeKeyPosition)
            = $this->current($data,$position);
        while($proficiencyEquivalents && $proficiencyEquivalentsPosition){
            $proficiencyIdMap[$typeKey]=[];
            list($leftType,$rightType)=preg_split('/\-/', $typeKey);
            if(!isset($this->domainValues['type'][$leftType])){
                throw new GeneralException($leftType, $typeKeyPosition,"left type is invalid",
                    InterfaceExceptionCode::MAPPING_PROFICIENCY_TYPE);
            }
            if(!isset($this->domainValues['type'][$rightType])){
                throw new GeneralException($rightType, $typeKeyPosition, "right type is invalid",
                        InterfaceExceptionCode::MAPPING_PROFICIENCY_TYPE);
            }
            $checkLeft = $this->mappingCheck[$leftType];
            $checkRight = $this->mappingCheck[$rightType];
            list($rightProficiency,$rightProficiencyPosition,$leftProficiency,$leftProficiencyPosition)
                = $this->current($proficiencyEquivalents, $proficiencyEquivalentsPosition);
            while($rightProficiency && $rightProficiencyPosition){
                if(!isset($checkLeft[$leftProficiency])) {
                    throw new GeneralException($leftProficiency, $leftProficiencyPosition,
                        "is not valid for $leftType",InterfaceExceptionCode::MAPPING_CHECK);
                }
                if(!isset($checkRight[$rightProficiency])) {
                    throw new GeneralException($rightProficiency, $rightProficiencyPosition,
                            "is not valid for $rightType", InterfaceExceptionCode::MAPPING_CHECK);
                }
                /** @var Value $leftValue */
                $leftValue=$this->domainValues['proficiency'][$leftProficiency];
                /** @var Value $rightValue */
                $rightValue=$this->domainValues['proficiency'][$rightProficiency];
                $proficiencyIdMap[$typeKey][$leftValue->getId()]=$rightValue->getId();
                list($rightProficiency, $rightProficiencyPosition, $leftProficiency, $leftProficiencyPosition)
                    = $this->next($proficiencyEquivalents, $proficiencyEquivalentsPosition);
            }
            list($proficiencyEquivalents, $proficiencyEquivalentsPosition, $typeKey, $typeKeyPosition)
                = $this->next($data,$position);
        }
        return $proficiencyIdMap;
    }

    /**
     * @param array $data
     * @param array $position
     * @return array
     * @throws GeneralException
     */
    private function buildAgeMapping(array $data, array $position):array
    {
        $ageIdByYear=[];
        list($agePart, $agePosition, $typeKey, $typeKeyPosition)
            = $this->current($data, $position);
        while($agePart && $agePosition){
            if(!isset($this->domainValues['type'][$typeKey])){
                throw new GeneralException($typeKey,$typeKeyPosition,"is invalid",
                    InterfaceExceptionCode::MAPPING_TYPE_AGE);
            }
            $ageIdByYear[$typeKey]=[];
            list($ageDivision, $ageDivisionPosition,$ageSpread,$ageSpreadPosition)
                = $this->current($agePart,$agePosition);
            while($ageDivision && $ageDivisionPosition){
                list($low,$high) = preg_split("/\-/",$ageSpread);
                $spread=$this->checkAgeSpread($low,$high);
                if(!$spread){
                    throw new GeneralException($ageSpread, $ageSpreadPosition, 'error in age spread',
                        InterfaceExceptionCode::MAPPING_AGE_SPREAD);
                }

                if(!isset($this->domainValues['age'][$ageDivision])) {
                    throw new GeneralException($ageDivision, $ageDivisionPosition,'is invalid',
                            InterfaceExceptionCode::MAPPING_AGE_INVALID);
                }
                /** @var Value $ageValue */
                $ageValue = $this->domainValues['age'][$ageDivision];
                for($i=$spread['low'];$i<=$spread['high'];$i++){
                    if(isset($ageIdByYear[$typeKey][$i])){
                        throw new GeneralException($ageSpread, $ageSpreadPosition, 'age spread overlap',
                                InterfaceExceptionCode::MAPPING_SPREAD_OVERLAP);
                    }
                    $ageIdByYear[$typeKey][$i]=$ageValue->getId();
                }

                list($ageDivision, $ageDivisionPosition, $ageSpread, $ageSpreadPosition)
                    = $this->next($agePart, $agePosition);

            }
            list($agePart, $agePosition, $typeKey, $typeKeyPosition)
                = $this->next($data, $position);

        }
        return $ageIdByYear;
    }

    /**
     * @param string $low
     * @param string $high
     * @return array|bool
     */
    private function checkAgeSpread(string $low, string $high)
    {
        $nlow = intval($low);
        $nhigh= intval($high);
        if(!($nlow<$nhigh)) {
            return false;
        }
        return ['low'=>$nlow,'high'=>$nhigh];
    }

}