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

    private $domainValueHash = [];

    private $modelByName = [];

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

    private $typeASelections=[];

    private $typeBSelections=[];

    private $proficiencyDropdowns=[];

    private $tiProficiencyDropdowns=[];

    private $mappings = [];

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
        $this->domainValueHash = $this->valueRepository->fetchDomainValueHash();
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

        $this->loadModels( next( $r['data'] ), next( $r['position'] ),
            key( $r['data'] ), key( $r['position'] ) );

        $setups=$this->buildSetups( next( $r['data'] ), next( $r['position'] ),
            key( $r['data'] ), key( $r['position'] ) );

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
                InterfaceExceptionCode::COMPETITION_KEYWORD );
        }
        /** @var Competition $competition */
        $competition = $this->competitionRepository->findOneBy( ['name' => $dataPart] );
        if (!$competition) {
            throw new GeneralException( $dataPart, $positionPart, "not found",
                InterfaceExceptionCode::COMPETITION_INVALID );
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
    private function loadModels($dataPart, $dataPosition, $dataKey, $positionKey)
    {
        if ($dataKey != 'models') {
            throw new GeneralException( $dataKey, $positionKey, "expected \"models\"",
                InterfaceExceptionCode::MODELS_KEYWORD );
        }
        list( $modelName, $position, , ) = $this->current( $dataPart, $dataPosition );
        while ($modelName && $position) {
            /** @var Model $model */
            $model = $this->modelRepository->findOneby( ['name' => $modelName] );
            if (!$model) {
                throw new GeneralException( $modelName, $position, "is an invalid model",
                    InterfaceExceptionCode::MODEL_INVALID );
            }
            $this->modelByName[$model->getName()]=$model;
            list( $modelName, $position, , ) = $this->next( $dataPart, $dataPosition );
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
                InterfaceExceptionCode::SETUPS_KEYWORD );
        }
        list( $participantsPart, $participantsPosition, $participantsKey, $participantsPositionKey )
            = $this->current( $dataPart, $positionPart );
        if ($participantsKey != 'participant-form') {
            throw new GeneralException( $participantsKey, $participantsPositionKey, "expected \"participant-form\"",
                InterfaceExceptionCode::PFORM_KEYWORD );
        }
        $participantsSetup=$this->buildParticipantForm( $participantsPart, $participantsPosition );
        return $participantsSetup;
    }


    /**
     * @param array $data
     * @param array $positions
     * @return array
     * @throws GeneralException
     */
    private function buildParticipantForm(array $data, array $positions){
        list($typeAPart, $typeAPartPosition, $typeAKey, $typeAKeyPosition)
            = $this->current($data, $positions);
        if($typeAKey != 'typeA') {
            throw new GeneralException($typeAKey, $typeAKeyPosition, "expected \"typeA\"",
                InterfaceExceptionCode::TYPEA_KEYWORD);
        }
        $this->buildTypeA($typeAPart,$typeAPartPosition);
        list($typeBPart, $typeBPartPosition, $typeBKey, $typeBKeyPosition) = $this->next($data,$positions);
        if($typeBKey != 'typeB') {
            throw new GeneralException($typeBKey, $typeBKeyPosition, "expected \"typeB\"",
                InterfaceExceptionCode::TYPEB_KEYWORD);
        }
        $this->buildTypeB($typeBPart,$typeBPartPosition);
        if($typeAKey != 'typeA') {
            throw new GeneralException($typeBKey, $typeBKeyPosition, "expected \"typeA\"",
                InterfaceExceptionCode::TYPEB_KEYWORD);
        }

        list($dropdownPart, $dropdownPartPosition, $dropdownKey, $dropdownKeyPosition)
            = $this->next($data, $positions);
        if($dropdownKey != 'proficiency-dropdown') {
            throw new GeneralException( $dropdownKey, $dropdownKeyPosition,
                "expected \"proficiency-dropdown\"",
                InterfaceExceptionCode::PROFDROP_KEYWORD);
        }
        $this->buildTypeADropLayer($dropdownPart,$dropdownPartPosition);
        list($tiDropdownPart, $tiDropdownPartPosition, $tiDropdownKey,$tiDropdownKeyPosition)
            = $this->next($data,$positions);
        if($tiDropdownKey != 'ti-proficiency-dropdown') {
            throw new GeneralException($tiDropdownKey, $tiDropdownKeyPosition,
                "expected \"ti-proficiency-dropdown\"",
                InterfaceExceptionCode::TIPROFICIENCY_KEYWORD);
        }
        $clientModels = [];
        $clientHash = [];
        foreach($this->modelByName as $modelName=>$model) {
            $clientModels[$modelName]=$model->getId();
        }
        foreach($this->domainValueHash as $domain=>$valueList){
            if(!isset($clientHash[$domain])){
                $clientHash[$domain]=[];
            }
            /**
             * @var string  $valueName
             * @var  Value $value
             */
            foreach($valueList as $valueName=>$value){
                $clientHash[$domain][$valueName]=$value->getId();
            }

        }
        $this->buildTiProficiency($tiDropdownPart,$tiDropdownPartPosition);
        $participantSetup=['typeA'=>$this->typeASelections,
                           'typeB'=>$this->typeBSelections,
                           'models'=> $clientModels,
                           'proficiency'=>$this->proficiencyDropdowns,
                           'ti-proficiency'=>$this->tiProficiencyDropdowns,
                           'domain-value-hash'=>$clientHash,
                           'descr'=>['proficiency'=>'$data[$typeAId][$typeBId][$modelId][genres-proficiencies]']];
        return $participantSetup;
    }


    /**
     * @param array $data
     * @param array $positions
     * @throws GeneralException
     */
    public function buildTypeA(array $data,array $positions)
    {

        list($typeA,$typeAPosition, , )=$this->current($data,$positions);
        while ($typeA) {
            if (!in_array( $typeA, ['Professional', 'Amateur'] )) {
                throw new GeneralException( $typeA, $typeAPosition,
                    'expected "Professional" or "Amateur"',
                    InterfaceExceptionCode::TYPEA_INVALID );
            }
            /** @var Value $typeAValue */
            $typeAValue = $this->domainValueHash['type'][$typeA];
            $this->typeASelections[$typeAValue->getName()]=$typeAValue->getId();
            list($typeA,$typeAPosition, , ) = $this->next($data,$positions);
        }
    }

    /**
     * @param array $data
     * @param array $positions
     * @throws GeneralException
     */
    public function buildTypeB(array $data,array $positions)
    {
        list($typeB,$typeBPosition, , )=$this->current($data,$positions);
        while ($typeB) {
            if (!in_array( $typeB, ['Teacher','Student'] )) {
                throw new GeneralException( $typeB, $typeBPosition,
                    'expected "Teacher" or "Student"',
                    InterfaceExceptionCode::TYPEB_INVALID );
            }
            /** @var Value $typeBValue */
            $typeBValue = $this->domainValueHash['type'][$typeB];
            $this->typeBSelections[$typeBValue->getName()]=$typeBValue->getId();
            list($typeB,$typeBPosition, , ) = $this->next($data,$positions);
        }
    }

    /**
     * @param array $data
     * @param array $positions
     * @throws GeneralException
     */
    private function buildTypeADropLayer(array $data,array $positions)
    {
        list($layer,$layerPosition,$layerKey,$layerKeyPosition)
            = $this->current($data,$positions);
        $expectedKeys = array_keys($this->typeASelections);
        while ($layer) {
            if (!in_array( $layerKey, $expectedKeys )) {
                $expectedString = join( '","', $expectedKeys );
                throw new GeneralException( $layerKey, $layerKeyPosition,
                    "expected \"$expectedString\"",
                    InterfaceExceptionCode::DROPA_INVALID );
            }
            $typeAId=$this->typeASelections[$layerKey];
            if(!isset($this->proficiencyDropdowns[$typeAId])){
                $this->proficiencyDropdowns[$typeAId]=[];
            }
            $this->buildTypeBDropLayer($layer,$layerPosition,$typeAId);
            list($layer,$layerPosition,$layerKey,$layerKeyPosition)
                = $this->next($data,$positions);

        }
    }

    /**
     * @param $data
     * @param $positions
     * @param $typeAId
     * @throws GeneralException
     */
    private function buildTypeBDropLayer($data,$positions,$typeAId)
    {

        list($layer,$layerPosition,$layerKey,$layerKeyPosition)
            = $this->current($data,$positions);
        $expectedKeys = array_keys($this->typeBSelections);

        while($layer){
            if (!in_array( $layerKey, $expectedKeys )) {
                $expectedString = join( '","', $expectedKeys );
                throw new GeneralException( $layerKey, $layerKeyPosition,
                    "expected \"$expectedString\"",
                    InterfaceExceptionCode::DROPB_INVALID );
            }
            $typeBId=$this->typeBSelections[$layerKey];
            if(!isset($this->proficiencyDropdowns[$typeAId][$typeBId])){
                $this->proficiencyDropdowns[$typeAId][$typeBId]=[];
            }
            $this->buildModelDropLayer($layer,$layerPosition,$typeAId,$typeBId);
            list($layer,$layerPosition,$layerKey,$layerKeyPosition)
                = $this->next($data,$positions);
        }
    }


    /**
     * @param array $data
     * @param array $positions
     * @param int $typeAId
     * @param int $typeBId
     * @throws GeneralException
     */
    private function buildModelDropLayer(array $data,array $positions,int $typeAId,int $typeBId)
    {
        list($layer,$layerPosition,$layerKey,$layerKeyPosition)
            = $this->current($data,$positions);
        $expectedKeys = array_keys($this->modelByName);
        while($layer){
            if (!in_array( $layerKey, $expectedKeys )) {
                $expectedString = join( '","', $expectedKeys );
                throw new GeneralException( $layerKey, $layerKeyPosition,
                    "expected \"$expectedString\"",
                    InterfaceExceptionCode::DROP_MODEL_INVALID);
            }
            /** @var Model $model */
            $model=$this->modelByName[$layerKey];
            $modelId = $model->getId();
            $this->buildGenresProficienciesLayer($layer,$layerPosition,$typeAId,$typeBId,$modelId);
            list($layer,$layerPosition,$layerKey,$layerKeyPosition)
                =$this->next($data,$positions);
        }
    }


    /**
     * @param $data
     * @param $positions
     * @param $typeAId
     * @param $typeBId
     * @param $modelId
     * @throws GeneralException
     */
    private function buildGenresProficienciesLayer($data,$positions,$typeAId,$typeBId,$modelId)
    {

        list($genres,$genresPosition,$genreKey,$genreKeyPosition)
            = $this->current($data,$positions);
        if($genreKey!='genres'){
            throw new GeneralException($genreKey,$genreKeyPosition,
                'expected "es"',
                InterfaceExceptionCode::GENRES_KEYWORD);
        }
        list($proficiencies,$proficienciesPosition,$proficienciesKey,$proficienciesKeyPosition)
            = $this->next($data,$positions);
        if($proficienciesKey!='proficiencies'){
            throw new GeneralException($proficienciesKey,$proficienciesKeyPosition,
                    'expected "proficiencies"',
                    InterfaceExceptionCode::PROFICIENCIES_KEYWORD);
        }
        $this->buildGenresProficienciesDetail($genres,$genresPosition,
                                              $proficiencies,$proficienciesPosition,
                                              $typeAId,$typeBId,$modelId)  ;
    }

    /**
     * @param array $genres
     * @param array $genresPosition
     * @param array $proficiencies
     * @param $proficienciesPosition
     * @param int $typeAId
     * @param int $typeBId
     * @param int $modelId
     * @throws GeneralException
     */

    private function buildGenresProficienciesDetail(array $genres, array $genresPosition,
                                                    array $proficiencies,$proficienciesPosition,
                                                    int $typeAId,int $typeBId,int $modelId)
    {
        $genresKeyStringPairs = $this->buildGenresKeyStringPairs($genres,$genresPosition);
        $proficienciesKeyStringPairs = $this->buildProficienciesKeyStringPairs($proficiencies,$proficienciesPosition);
        $this->proficiencyDropdowns[$typeAId][$typeBId][$modelId]
            =
            [
                'genres'=>$genresKeyStringPairs,
                'proficiencies'=>$proficienciesKeyStringPairs
            ];
    }


    /**
     * @param $data
     * @param $positions
     * @return array
     * @throws GeneralException
     */
    private function buildGenresKeyStringPairs($data,$positions)
    {
        list($genre,$genrePosition,,)=$this->current($data,$positions);
        $idStringPair = [];
        while($genre){
            $hasGenre = isset($this->domainValueHash['style'][$genre])||
                        isset($this->domainValueHash['substyle'][$genre]);
            if(!$hasGenre){
                throw new GeneralException($genre,$genrePosition,"is invalid",
                            InterfaceExceptionCode::GENRE_INVALID);
            }
            /** @var Value $value */
            $value = isset($this->domainValueHash['style'][$genre])?
                        $this->domainValueHash['style'][$genre]:
                        $this->domainValueHash['substyle'][$genre];
            $idStringPair[$value->getId()]=$value->getName();
            list($genre,$genrePosition,,)=$this->next($data,$positions);
        }
        return $idStringPair;
    }


    /**
     * @param $data
     * @param $positions
     * @return array
     * @throws GeneralException
     */
    private function buildProficienciesKeyStringPairs($data,$positions)
    {
        list($proficiency,$proficiencyPosition,,)=$this->current($data,$positions);
        $idStringPair = [];
        while($proficiency){
            if(!isset($this->domainValueHash['proficiency'][$proficiency])){
                throw new GeneralException($proficiency,$proficiencyPosition,
                    "is invalid",
                    InterfaceExceptionCode::PROFICIENCY_INVALID);
            }
            /** @var Value $value */
            $value = $this->domainValueHash['proficiency'][$proficiency];
            $idStringPair[$value->getName()]=$value->getId();
            list($proficiency,$proficiencyPosition,,)=$this->next($data,$positions);
        }
        return $idStringPair;
    }

    /**
     * @param $data
     * @param $positions
     * @throws GeneralException
     */
    public function buildTiProficiency($data,$positions)
    {
        list($dataPart,$positionsPart,$modelKey,$modelKeyPosition)
            = $this->current($data,$positions);
        while($dataPart){
            if(!isset($this->modelByName[$modelKey])){
                throw new GeneralException($modelKey,$modelKeyPosition,
                    "is invalid",
                    InterfaceExceptionCode::TIMODEL_INVALID);
            }
            /** @var Model $model */
            $model=$this->modelByName[$modelKey];
            list($genres,$genrePositions,$genreKey,$genreKeyPosition)
                = $this->current($dataPart,$positionsPart);
            if($genreKey!='genres'){
                throw new GeneralException($genreKey,$genreKeyPosition,
                    'expected "genres"',
                    InterfaceExceptionCode::GENRES_KEYWORD);
            }
            list($proficiencies,$proficienciesPosition,$proficienciesKey,$proficienciesKeyPosition)
                = $this->next($dataPart,$positionsPart);

            if($proficienciesKey!='proficiencies'){
                throw new GeneralException($proficienciesKey,$proficienciesKeyPosition,
                    'expected "proficiencies"',
                    InterfaceExceptionCode::PROFICIENCIES_KEYWORD);
            }

            $this->buildTiGenresProficienciesDetail($genres,$genrePositions,
                                                    $proficiencies,$proficienciesPosition,
                                                    $model->getId());
            list($dataPart,$positionsPart,$modelKey,$modelKeyPosition)
                = $this->next($data,$positions);
        }
    }

    /**
     * @param $genres
     * @param $genrePositions
     * @param $proficiencies
     * @param $proficiencyPositions
     * @param $modelId
     * @throws GeneralException
     */
    private function buildTiGenresProficienciesDetail($genres,$genrePositions,
                                                       $proficiencies, $proficiencyPositions,
                                                        $modelId)
    {
        $genresKeyStringPairs = $this->buildGenresKeyStringPairs($genres,$genrePositions);
        $proficiencyKeyStringPairs = $this->buildProficienciesKeyStringPairs($proficiencies,$proficiencyPositions);
        $this->tiProficiencyDropdowns[$modelId]=['genres'=>$genresKeyStringPairs,
                                                 'proficiencies'=>$proficiencyKeyStringPairs];
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
                    InterfaceExceptionCode::MAPPINGS_KEYWORD);
        }

        list($proficiencyPart,$proficiencyPosition,$proficiencyKey,$proficiencyKeyPosition)
            = $this->current($dataPart, $positionPart);
        if($proficiencyKey != 'proficiency'){
            throw new GeneralException($proficiencyKey, $proficiencyKeyPosition, "expected \"proficiency\"",
                InterfaceExceptionCode::PROFICIENCY_KEYWORD);
        }
        if(!isset($this->mappings['proficiency'])) {
            $this->mappings['proficiency']=[];
        }
        $this->buildProficiencyMapping($proficiencyPart,$proficiencyPosition);
        return $this->mappings;
    }


    /**
     * @param array $data
     * @param array $positions
     * @throws GeneralException
     */
    public function buildProficiencyMapping(array $data,array $positions){
        list($bottomModelData,$bottomModelDataPositions, $topModelKey,$topModelKeyPosition)
            = $this->current($data,$positions);
        while($bottomModelData) {
            if(!isset($this->modelByName[$topModelKey])) {
                throw new GeneralException($topModelKey,$topModelKeyPosition,
                            "is invalid model",
                    InterfaceExceptionCode::MODEL_INVALID_5112);
            }
            /** @var Model $model */
            $model = $this->modelByName[$topModelKey];
            $modelId = $model->getId();
            if(!isset($this->mappings['proficiency'][$modelId])){
                $this->mappings['proficiency'][$modelId]=[];
            }
            $this->buildProficiencyMappingLayer1($bottomModelData,$bottomModelDataPositions,$model->getId());
            list($bottomModelData, $bottomModelDataPositions, $topModelKey, $topModelKeyPosition)
                = $this->next($data,$positions);
        }
    }


    /**
     * @param array $data
     * @param array $positions
     * @param int $modelId1
     * @throws GeneralException
     */
    public function buildProficiencyMappingLayer1(array $data,array $positions,int $modelId1)
    {
        list($proficiencies,$proficienciesPositions,$bottomModelKey,$bottomModelKeyPosition)
            = $this->current($data,$positions);
        while($proficiencies){
            if(!isset($this->modelByName[$bottomModelKey])){
                throw new GeneralException($bottomModelKey,$bottomModelKeyPosition,
                    "is invalid",
                    InterfaceExceptionCode::MODEL_INVALID_5122);
            }
            $model = $this->modelByName[$bottomModelKey];
            $modelId2 = $model->getId();
            if(!isset($this->mappings['proficiency'][$modelId1][$modelId2])) {
                $this->mappings['proficiency'][$modelId1][$modelId2]=[];
            }
            $this->buildProficiencyMappingLayer2($proficiencies,$proficienciesPositions,$modelId1,$modelId2);
            list($proficiencies,$proficienciesPositions,$bottomModelKey,$bottomModelKeyPosition)
                =$this->next($data,$positions);
        }
    }

    /**
     * @param array $data
     * @param array $positions
     * @param int $modelId1
     * @param int $modelId2
     * @throws GeneralException
     */
    public function buildProficiencyMappingLayer2(array $data,array $positions,int $modelId1,int $modelId2) {
        list($proficiency2,$proficiency2Position,$proficiency1,$proficiency1Position)
            = $this->current($data,$positions);
        while($proficiency2) {
            if(!isset($this->domainValueHash['proficiency'][$proficiency1])){
                throw new GeneralException($proficiency1,$proficiency1Position,
                        "is invalid",
                        InterfaceExceptionCode::PROFICIENCY_INVALID_5124);
            }
            /** @var Value $value1 */
            $value1=$this->domainValueHash['proficiency'][$proficiency1];
            $proficiencyId1=$value1->getId();
            if(!isset($this->domainValueHash['proficiency'][$proficiency2])){
                throw new GeneralException($proficiency2,$proficiency2Position,
                    "is invalid",
                    InterfaceExceptionCode::PROFICIENCY_INVALID_5126);
            }
            /** @var Value $value2 */
            $value2 = $this->domainValueHash['proficiency'][$proficiency2];
            $proficiencyId2 = $value2->getId();
            if(!isset($this->mappings['proficiency'][$modelId1][$modelId2][$proficiencyId1])){
                $this->mappings['proficiency'][$modelId1][$modelId2][$proficiencyId1]=$proficiencyId2;
            }
            list($proficiency2,$proficiency2Position,$proficiency1,$proficiency1Position)
                = $this->next($data,$positions);
        }
    }

}