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



use App\Entity\Competition\Model;
use App\Entity\Models\Value;
use App\Entity\Sales\Iface\Participant;
use App\Exceptions\GeneralException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\IfaceRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;



class ParticipantPoolGenerator extends BaseParser
{


    /**
     * @var IfaceRepository
     */
    protected $ifaceRepository;

    protected $modelByName = [];

    protected $pools =
        ['medal' => [],
        'amateur' => [],
        'proam' => [],
        'medal-amateur' => [],
        'medal-proam' => [],
        'amateur-proam' => [],
        'medal-amateur-proam' => []];

    public function __construct(CompetitionRepository $competitionRepository,
                                ModelRepository $modelRepository,
                                IfaceRepository $ifaceRepository,
                                ValueRepository $valueRepository)
    {
        parent::__construct( $competitionRepository,
            $modelRepository,
            $valueRepository );
        $this->ifaceRepository = $ifaceRepository;
        $modelList = $modelRepository->findAll();
        /** @var Model $model */
        foreach ($modelList as $model) {
            $this->modelByName[$model->getName()] = $model;
        }
    }

    /**
     * @param $poolName
     * @param $typeA
     * @param $typeB
     * @param $sex
     * @param $genre
     * @param $proficiency
     * @param $age
     * @throws \Exception
     */
    private function checkInput($poolName,$typeA,$typeB,$sex,$genre,$proficiency,$age)
    {
        if(!isset($this->pools[$poolName])) {
            throw new \Exception("'$poolName' is invalid an unrecognized pool of competitors",9000);
        }
        if(!isset($this->pools[$poolName][$typeA][$typeB][$sex])){
            throw new \Exception("sex:'$sex' is invalid.",9000);
        }
        if(!isset($this->pools[$poolName][$typeA][$typeB][$sex][$genre])){
            throw new \Exception("genre:'$genre' is invalid.",9000);
        }
        if(!isset($this->pools[$poolName][$typeA][$typeB][$sex][$genre][$proficiency])){
            throw new \Exception("proficiency:'$proficiency' is invalid.",9000);
        }
        if(!isset($this->pools[$poolName][$typeA][$typeB][$sex][$genre][$proficiency][$age])){
            throw new \Exception("age:'$age' is invalid for $proficiency proficiency and $poolName pool",9000);
        }
    }

    public function getDomainValueHash(){
        return $this->domainValueHash;
    }

    public function getValueById(){
        return $this->valueById;
    }

    /**
     * @param $poolName
     * @param $sex
     * @param $genre
     * @param $proficiency
     * @param $age
     * @return mixed
     * @throws \Exception
     */
    public function getStudent($poolName,$sex,$genre,$proficiency,$age)
    {
        $this->checkInput($poolName,'Amateur','Student',$sex,$genre,$proficiency,$age);
        return $this->pools[$poolName]['Amateur']['Student'][$sex][$genre][$proficiency][$age];
    }

    /**
     * @param $poolName
     * @param $sex
     * @param $genre
     * @param $proficiency
     * @param $age
     * @return mixed
     * @throws \Exception
     */
    public function getProfessionalTeacher($poolName,$sex,$genre,$proficiency,$age)
    {
        $this->checkInput($poolName,'Professional','Teacher',$sex,$genre,$proficiency,$age);
        return $this->pools[$poolName]['Professional']['Teacher'][$sex][$genre][$proficiency][$age];
    }

    /**
     * @param $poolName
     * @param $sex
     * @param $genre
     * @param $proficiency
     * @param $age
     * @return mixed
     * @throws \Exception
     */
    public function getAmateurTeacher($poolName,$sex,$genre,$proficiency,$age)
    {
        $this->checkInput($poolName,'Amateur','Teacher',$sex,$genre,$proficiency,$age);
        return $this->pools[$poolName]['Amateur']['Teacher'][$sex][$genre][$proficiency][$age];
    }





    /**
     * @param string $yaml
     * @return array
     * @throws GeneralException
     * @throws \Exception
     */
    public function parse(string $yaml)
    {
        $r = $this->fetchPhpArray( $yaml );
        if (key( $r['data'] ) == 'comment') {
            next( $r['data'] );
            next( $r['position'] );
        }
        list( $competitionName, $competitionNamePosition, $competitionKey, $competitionKeyPosition )
            = $this->current( $r['data'], $r['position'] );
        $this->fetchCompetition( $competitionName, $competitionNamePosition,
            $competitionKey, $competitionKeyPosition );

        list( $participantPool, $participantPoolPositions, $participantPoolKey, $participantPoolKeyPosition )
            = $this->next( $r['data'], $r['position'] );
        $this->buildParticipantPool( $participantPool, $participantPoolPositions, $participantPoolKey, $participantPoolKeyPosition );
        return $this->pools;
    }

    /**
     * @param array $data
     * @param array $positions
     * @param string $key
     * @param string $keyPosition
     * @throws GeneralException
     */
    private function buildParticipantPool(array $data, array $positions, string $key, string $keyPosition)
    {
        if ($key != 'participant-pool') {
            throw new GeneralException( $key, $keyPosition, 'expected "participant-pool"',
                ParticipantExceptionCode::PARTICIPANT_POOL );
        }
        list( $batch, $batchPosition, , ) = $this->current( $data, $positions );
        while ($batch) {
            $this->buildParticipantBatch( $batch, $batchPosition );
            list( $batch, $batchPosition, , ) = $this->next( $data, $positions );
        }
    }

    /**
     * @param $data
     * @param $position
     * @throws GeneralException
     */
    private function buildParticipantBatch($data, $position)
    {
        $component = [];
        $componentPosition = [];
        list( $dataPart, $positionPart, $dataKey, $positionKey ) = $this->current( $data, $position );
        while ($dataPart) {
            $acceptedKeys = ['models', 'genres', 'proficiencies', 'ages', 'sex', "typeA", "typeB"];
            if (!in_array( $dataKey, $acceptedKeys )) {
                $expected = join( '","', $acceptedKeys );
                throw new GeneralException( $dataKey, $positionKey, "expected \"$expected\"",
                    ParticipantExceptionCode::INVALID_KEY );
            }
            $component[$dataKey] = $dataPart;
            $componentPosition[$dataKey] = $positionPart;
            list( $dataPart, $positionPart, $dataKey, $positionKey ) = $this->next( $data, $position );
        }
        list( $poolName, $models ) = $this->selectPools( $component['models'], $componentPosition['models'] );
        $this->layerTypeABSex( $poolName, $models, $component, $componentPosition );
        //$this->layerGenre($poolName,$models,$component,$componentPosition);
    }

    /**
     * @param $models
     * @param $modelPositions
     * @return array|null
     * @throws GeneralException
     */
    private function selectPools($models, $modelPositions)
    {
        $collection = [];
        list( $singleModel, $singleModelPosition, , ) = $this->current( $models, $modelPositions );
        while ($singleModel) {
            if (!isset( $this->modelByName[$singleModel] )) {
                throw new GeneralException( $singleModel, $singleModelPosition, "invalid model",
                    ParticipantExceptionCode::INVALID_MODEL );
            }
            $modelObj = $this->modelByName[$singleModel];
            $collection[$singleModel] = $modelObj;
            list( $singleModel, $singleModelPosition ) = $this->next( $models, $modelPositions );
        }
        if (isset( $collection["ISTD Medal Exams"] )
            && isset( $collection["Georgia DanceSport Amateur"] )
            && isset( $collection["Georgia DanceSport ProAm"] )) {
            return ['medal-amateur-proam', $collection];
        }
        if (isset( $collection["ISTD Medal Exams"] )
            && isset( $collection["Georgia DanceSport Amateur"] )) {
            return ['medal-amateur', $collection];
        }
        if (isset( $collection["ISTD Medal Exams"] )
            && isset( $collection["Georgia DanceSport ProAm"] )) {
            return ['medal-proam', $collection];
        }
        if (isset( $collection["Georgia DanceSport Amateur"] )
            && isset( $collection["Georgia DanceSport ProAm"] )) {
            return ['amateur-proam', $collection];
        }
        if (isset( $collection["ISTD Medal Exams"] )) {
            return ['medal', $collection];
        }
        if (isset( $collection["Georgia DanceSport Amateur"] )) {
            return ['amateur', $collection];
        }
        if (isset( $collection['Georgia DanceSport ProAm'] )) {
            return ['proam', $collection];
        }
        return null;
    }


    /**
     * @param $poolName
     * @param $models
     * @param $component
     * @param $componentPosition
     * @throws GeneralException
     */
    private function layerTypeABSex($poolName, $models, $component, $componentPosition)
    {
        $typeA = $component['typeA'];
        $typeAPosition = $componentPosition['typeA'];
        $typeB = $component['typeB'];
        $typeBPosition = $componentPosition['typeB'];
        if (!isset( $this->domainValueHash['type'][$typeA] )) {
            throw new GeneralException( $typeA, $typeAPosition, "invalid type.",
                ParticipantExceptionCode::INVALID_TYPE );
        }
        if (!isset( $this->domainValueHash['type'][$typeB] )) {
            throw new GeneralException( $typeB, $typeBPosition, "invalid type.",
                ParticipantExceptionCode::INVALID_TYPE );
        }
        if (!isset( $this->pools[$poolName][$typeA] )) {
            $this->pools[$poolName][$typeA] = [];
        }
        if (!isset( $this->pools[$poolName][$typeA][$typeB] )) {
            $this->pools[$poolName][$typeA][$typeB] = [];
        }
        foreach ($component['sex'] as $idx => $sex) {
            if (!in_array( $sex, ['M', 'F'] )) {
                throw new GeneralException( $component['sex'][$idx], $componentPosition['sex'][$idx],
                    "expected 'M','F'", ParticipantExceptionCode::INVALID_SEX );
            }
            if (!isset( $this->pools[$poolName][$typeA][$typeB][$sex] )) {
                $this->pools[$poolName][$typeA][$typeB][$sex] = [];
            }
            $this->layerGenre( $poolName, $models,
                $component, $componentPosition,
                $typeA, $typeB, $sex );
        }


    }

    /**
     * @param string $poolName
     * @param array $models
     * @param array $component
     * @param array $componentPosition
     * @param string $typeA
     * @param string $typeB
     * @param string $sex
     * @throws GeneralException
     */
    private function layerGenre(string $poolName, array $models,
                                array $component, array $componentPosition,
                                string $typeA, string $typeB, string $sex)
    {
        $positionGenre = current( $componentPosition['genres'] );
        foreach ($component['genres'] as $genre) {
            if (!$this->hasDomainValue( 'style', $genre ) && !$this->hasDomainValue( 'substyle', $genre )) {
                throw new GeneralException( $genre, $positionGenre, "is invalid",
                    ParticipantExceptionCode::INVALID_GENRE );
            }
            if (!isset( $this->pools[$poolName][$typeA][$typeB][$sex][$genre] )) {
                $this->pools[$poolName][$typeA][$typeB][$sex][$genre] = [];
            }
            $this->layerProficiency( $poolName, $models,
                $component, $componentPosition,
                $typeA, $typeB, $sex, $genre );
            $positionGenre = next( $componentPosition['genres'] );
        }
    }

    /**
     * @param string $poolName
     * @param array $models
     * @param array $component
     * @param array $componentPosition
     * @param string $typeA
     * @param string $typeB
     * @param string $sex
     * @param string $genre
     * @throws GeneralException
     */
    private function layerProficiency(string $poolName, array $models,
                                      array $component, array $componentPosition,
                                      string $typeA, string $typeB, string $sex, string $genre)
    {
        $positionProficiency = current( $componentPosition['proficiencies'] );
        foreach ($component['proficiencies'] as $proficiency) {
            if (!$this->hasDomainValue( 'proficiency', $proficiency )) {
                throw new GeneralException( $proficiency, $positionProficiency, "is invalid",
                    ParticipantExceptionCode::INVALID_PROFICIENCY );
            }
            if (!isset( $this->participants[$genre][$proficiency] )) {
                $this->pools[$poolName][$typeA][$typeB][$sex][$genre][$proficiency] = [];
            }
            $this->layerAge( $poolName, $models,
                            $component, $componentPosition,
                            $typeA, $typeB, $sex,
                            $genre, $proficiency );
            $positionProficiency = next( $componentPosition['proficiencies'] );
        }
    }


    /**
     * @param string $poolName
     * @param array $models
     * @param $component
     * @param $componentPosition
     * @param string $typeA
     * @param string $typeB
     * @param string $sex
     * @param string $genre
     * @param string $proficiency
     * @throws GeneralException
     */
    private function layerAge(string $poolName, array $models,
                              $component, $componentPosition,
                              string $typeA, string $typeB, string $sex,
                              string $genre, string $proficiency)
    {
        $ages = $component['ages'];
        $agesPosition = $componentPosition['ages'];
        if (strpos( $ages, '-' ) == 0) {
            throw new GeneralException( $ages, $agesPosition, "invalid age range",
                ParticipantExceptionCode::INVALID_RANGE );
        }
        list( $low, $high ) = explode( '-', $ages );
        if (!(is_numeric( $low ) && is_numeric( $high ))) {
            throw new GeneralException( $ages, $agesPosition, "invalid age range",
                ParticipantExceptionCode::INVALID_RANGE );
        }
        $nlow = intval( $low );
        $nhigh = intval( $high );
        if (!(is_int( $nlow ) && is_int( $nhigh ) && ($nlow <= $nhigh))) {
            throw new GeneralException( $ages, $agesPosition, "invalid age range",
                ParticipantExceptionCode::INVALID_RANGE );
        }

        for ($nage = $nlow; $nage <= $nhigh; $nage++) {
            /** @var string $type */
            if (!isset( $this->pools[$poolName][$typeA][$typeB][$sex][$genre][$proficiency][$nage] )) {
                /** @var Participant $participant */
                $participant = new Participant();
                $first = $genre . '-' . $proficiency;
                $last = $typeA.'-'.$sex.$nage;
                /** @var Value $proficiencyValue */
                $proficiencyValue = $this->getDomainValue( 'proficiency', $proficiency );
                $genreValue = $this->hasDomainValue( 'style', $genre ) ?
                    $this->getDomainValue( 'style', $genre ) :
                    $this->getDomainValue( 'substyle', $genre );

                $participant->setFirst( $first )
                    ->setLast( $last )
                    ->setSex( $sex )
                    ->setYears( $nage )
                    ->addGenreProficiency( $genreValue, $proficiencyValue );;
                /** @var Model $model */
                foreach ($models as $model) {
                    $participant->addModel( $model );
                }
                /** @var Value $typeAValue */
                $typeAValue = $this->getDomainValue( 'type', $typeA );
                $participant->setTypeA( $typeAValue );
                /** @var Value $typeBValue */
                $typeBValue = $this->getDomainValue( 'type', $typeB );
                $participant->setTypeB( $typeBValue );
                switch($poolName){
                    case 'medal':
                        $participant->addModel($this->modelByName['ISTD Medal Exams']);
                        break;
                    case 'amateur':
                        $participant->addModel($this->modelByName['Georgia DanceSport Amateur']);
                        break;
                    case 'proam':
                        $participant->addModel($this->modelByName['Georgia DanceSport ProAm']);
                        break;
                    case 'medal-amateur':
                        $participant->addModel($this->modelByName['ISTD Medal Exams']);
                        $participant->addModel($this->modelByName['Georgia DanceSport Amateur']);
                        break;
                    case 'medal-proam':
                        $participant->addModel($this->modelByName['ISTD Medal Exams']);
                        $participant->addModel($this->modelByName['Georgia DanceSport ProAm']);
                        break;
                    case 'amateur-proam':
                        $participant->addModel($this->modelByName['Georgia DanceSport Amateur']);
                        $participant->addModel($this->modelByName['Georgia DanceSport ProAm']);
                        break;
                    case 'medal-amateur-proam':
                        $participant->addModel($this->modelByName['ISTD Medal Exams']);
                        $participant->addModel($this->modelByName['Georgia DanceSport Amateur']);
                        $participant->addModel($this->modelByName['Georgia DanceSport ProAm']);
                }
                $this->pools[$poolName][$typeA][$typeB][$sex][$genre][$proficiency][$nage] = $participant;
            }
        }
    }
}