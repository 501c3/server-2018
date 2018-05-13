<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/16/18
 * Time: 3:40 PM
 */

namespace App\Doctrine\Competition;


use App\Doctrine\Builder;
use App\Entity\Competition\Competition;
use App\Entity\Competition\Event;
use App\Entity\Competition\Model as CompetitionModel;
use App\Entity\Competition\Player;
use App\Entity\Competition\Session;
use App\Entity\Competition\Subevent;
use App\Entity\Models\Model;
use App\Entity\Models\Value;
use App\Exceptions\GeneralException;
use App\Exceptions\SequenceExceptionCode;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\EventRepository as CompetitionEventRepository;
use App\Repository\Competition\ScheduleRepository;
use App\Repository\Competition\ModelRepository as CompetitionModelRepository;
use App\Repository\Competition\PlayerRepository as CompetitionPlayerRepository;
use App\Repository\Competition\SessionRepository;
use App\Repository\Competition\SubeventRepository as CompetitionSubeventRepository;
use App\Repository\Models\DomainRepository;
use App\Repository\Models\EventRepository as ModelEventRepository;
use App\Repository\Models\ModelRepository;
use App\Repository\Models\PlayerRepository as ModelPlayerRepository;
use App\Repository\Models\SubeventRepository as ModelSubeventRepository;
use App\Repository\Models\ValueRepository;
use App\Subscriber\Status;
use App\Utils\YamlPosition;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface;


class SequenceBuilder extends Builder
{

    /**
     * @var ModelRepository
     */
    private $modelRepository;
    /**
     * @var DomainRepository
     */
    private $domainRepository;
    /**
     * @var ValueRepository
     */
    private $valueRepository;
    /**
     * @var ModelPlayerRepository
     */
    private $modelPlayerRepository;
    /**
     * @var ModelEventRepository
     */
    private $modelEventRepository;
    /**
     * @var ModelSubeventRepository
     */
    private $modelSubeventRepository;
    /**
     * @var CompetitionRepository
     */
    private $competitionRepository;
    /**
     * @var CompetitionModelRepository
     */
    private $competitionModelRepository;
    /**
     * @var CompetitionPlayerRepository
     */
    private $competitionPlayerRepository;
    /**
     * @var CompetitionEventRepository
     */
    private $competitionEventRepository;
    /**
     * @var CompetitionSubeventRepository
     */
    private $competitionSubeventRepository;
    /**
     * @var SessionRepository
     */
    private $sessionRepository;

    /**
     * @var ScheduleRepository
     */
    private $scheduleRepository;

    /** @var array  */
    private $modelDomainValues = [];

    /** @var array*/
    private $competitionEvents = [];

    /** @var array  */
    private $playerLookup = [];

    /** @var array  */
    private $competitionModel = [];

    /** @var array  */
    private $models = [];

    /** @var int  */
    private $sequence = 0;

    /**
     * SequenceBuilder constructor.
     * @param ModelRepository $modelRepository
     * @param DomainRepository $domainRepository
     * @param ValueRepository $valueRepository
     * @param ModelPlayerRepository $modelPlayerRepository
     * @param ModelEventRepository $modelEventRepository
     * @param ModelSubeventRepository $modelSubeventRepository
     * @param CompetitionRepository $competitionRepository
     * @param CompetitionModelRepository $competitionModelRepository
     * @param CompetitionPlayerRepository $competitionPlayerRepository
     * @param CompetitionEventRepository $competitionEventRepository
     * @param CompetitionSubeventRepository $competitionSubeventRepository
     * @param SessionRepository $sessionRepository
     * @param ScheduleRepository $scheduleRepository
     * @param TraceableEventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        ModelRepository $modelRepository,
        DomainRepository $domainRepository,
        ValueRepository $valueRepository,
        ModelPlayerRepository $modelPlayerRepository,
        ModelEventRepository $modelEventRepository,
        ModelSubeventRepository $modelSubeventRepository,
        CompetitionRepository $competitionRepository,
        CompetitionModelRepository $competitionModelRepository,
        CompetitionPlayerRepository $competitionPlayerRepository,
        CompetitionEventRepository $competitionEventRepository,
        CompetitionSubeventRepository $competitionSubeventRepository,
        SessionRepository $sessionRepository,
        ScheduleRepository $scheduleRepository,
        TraceableEventDispatcherInterface $eventDispatcher = null)
    {
        $this->modelRepository = $modelRepository;
        $this->domainRepository = $domainRepository;
        $this->valueRepository = $valueRepository;
        $this->modelPlayerRepository = $modelPlayerRepository;
        $this->modelEventRepository = $modelEventRepository;
        $this->modelSubeventRepository = $modelSubeventRepository;
        $this->competitionRepository = $competitionRepository;
        $this->competitionModelRepository = $competitionModelRepository;
        $this->competitionPlayerRepository = $competitionPlayerRepository;
        $this->competitionEventRepository = $competitionEventRepository;
        $this->competitionSubeventRepository = $competitionSubeventRepository;
        $this->sessionRepository = $sessionRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @param string $yamlText
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function build(string $yamlText){
        $r = YamlPosition::parse($yamlText);
        $lineCount=YamlPosition::getLineCount();
        $this->sendStatus(Status::COMMENCE,$lineCount);
        if(key($r['data'])=='comment'){
            next($r['data']); next($r['position']);
        }
        $competition=$this->addCompetition(current($r['data']),current($r['position']),
                                           key($r['data']), key($r['position']));

        $this->loadModelDomainValues(next($r['data']),next($r['position']),
                                     key($r['data']),key($r['position']));
        foreach($this->models as $model){
            $this->loadEvents($model);
            $this->loadPlayers($model);
            $modelId=$model->getId();
            $playerLookup=$this->playerLookup[$modelId];
            $this->competitionModel[$model->getName()]->setPlayerLookup($playerLookup);
        }
        $this->competitionModelRepository->getEntityManager()->flush();

        $this->loadSequence($competition,
                            next($r['data']),next($r['position']),
                            key($r['data']),key($r['position']));

        $this->sendStatus(Status::COMPLETE,100);
    }

    /**
     * @param $dataPart
     * @param $positionPart
     * @param $dataKey
     * @param $positionKey
     * @return Competition|null|object
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function addCompetition($dataPart,$positionPart,$dataKey,$positionKey)
    {
        if ($dataKey != 'competition') {
            throw new GeneralException($dataKey, $positionKey, "expected \"competition\"",
                                       SequenceExceptionCode::COMPETITION);
        }
        $competition=$this->competitionRepository->findOneBy(['name'=>$dataPart]);

        if($competition){
            throw new GeneralException($dataPart,$positionPart,"previously loaded",
                                        SequenceExceptionCode::PREVIOUS_LOAD);
        }
        $competition = new Competition();
        $competition->setName($dataPart);
        $em=$this->competitionRepository->getEntityManager();
        $em->persist($competition);
        $em->flush();
        return $competition;
    }

    /**
     * @param $dataPart
     * @param $dataPosition
     * @param $dataKey
     * @param $positionKey
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function loadModelDomainValues($dataPart,$dataPosition,$dataKey,$positionKey)
    {
        if($dataKey!='models'){
            throw new GeneralException($dataKey, $positionKey, "expected \"models\"",
                                        SequenceExceptionCode::MODELS);
        }
        list($modelName,$position, , ) = $this->current($dataPart,$dataPosition);
        while($modelName && $position){
            /** @var Model $model */
            $model=$this->modelRepository->findOneby(['name'=>$modelName]);
            if(!$model){
                throw new GeneralException($modelName,$position, "is an invalid model",
                                            SequenceExceptionCode::INVALID_MODEL);
            }
            $this->loadDomainValues($model);
            list($modelName,$position, , )=$this->next($dataPart, $dataPosition);
        }
    }

    /**
     * @param Model $model
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    private function loadDomainValues(Model $model){
        $modelName=$model->getName();

        if(!isset($this->competitionModel[$modelName])){
            $competitionModel=new CompetitionModel();
            $modelId = $model->getId();
            $competitionModel->setId($modelId)
                             ->setName($model->getName());
            $em=$this->competitionModelRepository->getEntityManager();
            $em->persist($competitionModel);
            $em->flush();
            $this->competitionModel[$modelName]=$competitionModel;
            $this->modelDomainValues[$modelName]=[];
            $this->models[$modelId] = $model;
        }
        $domainValues=$this->valueRepository->fetchDomainValues($model);

        foreach($domainValues as $value){
            /** @var Value $value*/
            $domainName=$value->getDomain()->getName();
            if(!isset($this->modelDomainValues[$modelName][$domainName])){
                $this->modelDomainValues[$modelName][$domainName]=[];
            }
            $this->modelDomainValues[$modelName][$domainName][$value->getName()]=$value;
        }
    }

    /**
     * @param Competition $competition
     * @param array $dataPart
     * @param array $positionPart
     * @param string $dataKey
     * @param string $positionKey
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    private function loadSequence(Competition $competition,
                                  array $dataPart, array $positionPart,
                                  string $dataKey, string $positionKey)
    {
        if($dataKey != 'sequence'){
            throw new GeneralException($dataKey,$positionKey, "expected \"sequence\"",
                                        SequenceExceptionCode::SEQUENCE);
        }
        $em=$this->sessionRepository->getEntityManager();
        list($sessionDataPart, $sessionPositionPart, $sessionKey, )
                = $this->current($dataPart, $positionPart);
        while($sessionDataPart && $sessionPositionPart){
            $session = new Session();
            $session->setName($sessionKey)
                    ->setCompetition($competition);
            $em->persist($session);
            $em->flush();
            list($modelCollection, $modelCollectionPosition, , )=
                $this->current($sessionDataPart, $sessionPositionPart);
            while($modelCollection && $modelCollectionPosition){
                $this->sequenceModel($competition, $modelCollection, $modelCollectionPosition);
                list($modelCollection, $modelCollectionPosition, , )=
                    $this->next($sessionDataPart, $sessionPositionPart);
            }
            list($sessionDataPart, $sessionPositionPart, $sessionKey, )
                = $this->next($dataPart, $positionPart);
        }
    }

    /**
     * @param Competition $competition
     * @param $dataPart
     * @param $positionPart
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function sequenceModel(Competition $competition, $dataPart,$positionPart)
    {
        list($modelDataPart, $modelPositionPart, $modelKey, $modelPositionKey)
            = $this->current($dataPart,$positionPart);
        while($modelDataPart && $modelPositionPart){
            if(!isset($this->competitionModel[$modelKey])){
                throw new GeneralException($modelKey, $modelPositionKey, "is not recognized",
                                            SequenceExceptionCode::UNRECOGNIZED_MODEL);
            }
            $model = $this->competitionModel[$modelKey];
            $this->sequenceStyle($competition, $model, $modelDataPart,$modelPositionPart);
            list($modelDataPart, $modelPositionPart,$modelKey, $modelPositionKey)
                = $this->next($dataPart,$positionPart);
        }
    }

    /**
     * @param Competition $competition
     * @param CompetitionModel $model
     * @param array $dataPart
     * @param array $positionPart
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function sequenceStyle(Competition $competition, CompetitionModel $model,
                                  array $dataPart, array $positionPart)
    {
        $modelName = $model->getName();
        list($styleCollection, $styleCollectionPosition, $styleKey, $stylePositionKey)
            = $this->current($dataPart,$positionPart);
        while($styleCollection && $styleCollectionPosition){
            if(!isset($this->modelDomainValues[$modelName]['style'][$styleKey])){
                throw new GeneralException($styleKey, $stylePositionKey, 'invalid style',
                                            SequenceExceptionCode::INVALID_STYLE);
            }
            $this->sequenceSubstyle($competition, $model,
                                $styleCollection, $styleCollectionPosition);
            list($styleCollection, $styleCollectionPosition, $styleKey, $stylePositionKey) = $this->next($dataPart,$positionPart);
        }

    }

    /**
     * @param Competition $competition
     * @param CompetitionModel $model
     * @param array $dataPart
     * @param array $positionPart
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function sequenceSubstyle(Competition $competition, CompetitionModel $model,
                                     array $dataPart, array $positionPart)
    {
        $modelName=$model->getName();
        list($proficiencyPart, $proficiencyPartPosition, $substyleKey, $substylePositionKey)
            = $this->current($dataPart, $positionPart);

        while($proficiencyPart && $proficiencyPartPosition){
            if(!isset($this->modelDomainValues[$modelName]['substyle'][$substyleKey])){
                throw new GeneralException($substyleKey, $substylePositionKey, "invalid substyle",
                                            SequenceExceptionCode::INVALID_SUBSTYLE);
            }
            $substyle = $this->modelDomainValues[$modelName]['substyle'][$substyleKey];
            $this->sequenceProficiency($competition, $model, $substyle,
                                        $proficiencyPart, $proficiencyPartPosition);
            list($proficiencyPart, $proficiencyPartPosition, $substyleKey, $substylePositionKey)
                = $this->next($dataPart, $positionPart);
        }

    }

    /**
     * @param Competition $competition
     * @param CompetitionModel $model
     * @param Value $substyle
     * @param array $dataPart
     * @param array $positionPart
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function sequenceProficiency(Competition $competition, CompetitionModel $model,
                                        Value $substyle, array $dataPart, array $positionPart)
    {
        $modelName=$model->getName();
        list($agePart, $agePartPosition, $proficiencyKey, $proficiencyKeyPosition)
            = $this->current($dataPart,$positionPart);
        while($agePart && $agePartPosition){
            if(!isset($this->modelDomainValues[$modelName]['proficiency'][$proficiencyKey])){
                throw new GeneralException($proficiencyKey, $proficiencyKeyPosition, "invalid proficiency",
                                            SequenceExceptionCode::INVALID_PROFICIENCY);
            }
            $proficiency = $this->modelDomainValues[$modelName]['proficiency'][$proficiencyKey];
            $this->sequenceAge($competition, $model, $substyle,$proficiency,
                                    $agePart,$agePartPosition);
            list($agePart, $agePartPosition, $proficiencyKey, $proficiencyKeyPosition)
                = $this->next($dataPart,$positionPart);
        }
    }

    /**
     * @param Competition $competition
     * @param CompetitionModel $model
     * @param Value $substyle
     * @param Value $proficiency
     * @param array $dataPart
     * @param array $positionPart
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function sequenceAge(Competition $competition,  CompetitionModel $model,
                                 Value $substyle, Value $proficiency,
                                 array $dataPart, array $positionPart)
    {
        $modelName=$model->getName();
        list($agePart, $agePartPosition, , )
            = $this->current($dataPart, $positionPart);

        while($agePart && $agePartPosition){
            if(!isset($this->modelDomainValues[$modelName]['age'][$agePart])){
                throw new GeneralException($agePart, $agePartPosition, 'invalid age',
                                            SequenceExceptionCode::INVALID_AGE);
            }
            $age=$this->modelDomainValues[$modelName]['age'][$agePart];
            $this->buildEventSubevent($competition,$model,$substyle,$proficiency,$age);
            list($agePart,$agePartPosition, , ) = $this->next($dataPart,$positionPart);
        }
    }

    /**
     * @param Competition $competition
     * @param CompetitionModel $competitionModel
     * @param Value $substyle
     * @param Value $proficiency
     * @param Value $age
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    private function buildEventSubevent(Competition $competition,CompetitionModel $competitionModel,
                                        Value $substyle, Value $proficiency, Value $age)
    {
        $em = $this->competitionSubeventRepository->getEntityManager();
        $model=$this->models[$competitionModel->getId()];
        $subevents=$this->modelSubeventRepository->fetchSubevents($model,[$substyle,$proficiency,$age]);
        foreach($subevents as $subevent){
            $subevent=$this->buildSubevent($subevent,$competition,$competitionModel);
            $em->persist($subevent);
        }
        $em->flush();
    }

    /**
     * @param array $subevent
     * @param Competition $competition
     * @param CompetitionModel $model
     * @return Subevent
     * @throws \Exception
     */
    private function buildSubevent(array $subevent,Competition $competition, CompetitionModel $model)
    {

        ++$this->sequence;
        $values = $subevent['value'];
        $preJSON=$this->valuesToPreJSON($values);
        $competitionSubevent = new Subevent();
        $competitionSubevent->setCompetition($competition)
                            ->setSequence($this->sequence)
                            ->setModel($model)
                            ->setEventId($subevent['event']['id'])
                            ->setId($subevent['id'])
                            ->setValue($preJSON);
        return $competitionSubevent;
    }

    /**
     * @param array $values
     * @return array
     * @throws \Exception
     */
    private function valuesToPreJSON(array $values)
    {
        $dances = [];
        $preJSON = [];
        /** @var Value $value */
        foreach($values as $value){
            $name=$value['name'];
            $abbr=$value['abbr'];
            $domain=$value['domain']['name'];
            switch ($domain){
                case 'style':
                case 'substyle':
                case 'proficiency':
                case 'age':
                case 'type':
                case 'tag':
                    $preJSON[$domain]=$name;
                    break;
                case 'dance':
                    array_push($dances, $abbr);
                    break;
                default:
                    $message = sprintf("\"$domain\" is an invalid domain at line %d in %s.", __LINE__, __FILE__);
                    throw new \Exception($message,4000);
            }
        }
        if(count($dances)){
            $preJSON['dances']=$dances;
        }
        return $preJSON;
    }


    /**
     * @param Model $model
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    private function loadEvents(Model $model)
    {
        $results=$this->modelEventRepository->fetchEvents($model);
        $competitionModel = $this->competitionModel[$model->getName()];
        $modelId=$model->getId();
        $em=$this->competitionEventRepository->getEntityManager();
        if(!isset($this->competitionEvents[$modelId])){
            $this->competitionEvents[$modelId]=[];
        }
        foreach($results as $result){
            $event=$this->buildEvent($competitionModel, $result);
            $em->persist($event);
            $this->competitionEvents[$modelId][$event->getId()]=$event;
        }
        $em->flush();
    }

    /**
     * @param CompetitionModel $model
     * @param array $data
     * @return Event
     * @throws \Exception
     */
    private function buildEvent(CompetitionModel $model, array $data){
        $values = $data['value'];
        $etag = $data['tag'];
        $event = new Event();
        $event->setModel($model)
                ->setId($data['id'])
                ->setTag(substr($etag['name'],0,1))
                ->setValue($this->valuesToPreJSON($values));
        return $event;
    }

    /**
     * @param Model $model
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    private function loadPlayers(Model $model)
    {
        $results=$this->modelPlayerRepository->fetchPlayers($model);
        $competitionModel = $this->competitionModel[$model->getName()];
        $em=$this->competitionPlayerRepository->getEntityManager();
        foreach($results as $result){
            /** @var  Player $player */
            $player=$this->buildPlayer($competitionModel,$result);
            $em->persist($player);
        }
        $em->flush();
    }

    /**
     * @param CompetitionModel $model
     * @param array $data
     * @return Player
     * @throws \Exception
     */
    private function buildPlayer(CompetitionModel $model, array $data):Player
    {
        $preJSON = $this->valuesToPreJSON($data['value']);
        $this->buildPlayerLookup($model->getId(),$data['id'],$data['value']);
        $events = $data['event'];
        $player = new Player();
        $player->setModel($model)
                ->setId($data['id'])
                ->setValue($preJSON);
        foreach($events as $event){
            $competitionEvent = $this->competitionEvents[$model->getId()][$event['id']];
            $player->getEventModel()->add($competitionEvent);
        }
        return $player;
    }


    /**
     * @param int $modelId
     * @param int $playerId
     * @param array $values
     */
    private function buildPlayerLookup(int $modelId, int $playerId, array $values)
    {
        if(!isset($this->playerLookup[$modelId])){
            $this->playerLookup[$modelId]=[];
        }
        $cls = array();
        foreach($values as $value){
            $id=$value['id'];
            $domain=$value['domain']['name'];
            $cls[$domain]=$id;
        }
        $genreId=isset($cls['style'])?$cls['style']:$cls['substyle'];
        $proficiencyId=$cls['proficiency'];
        $ageId=$cls['age'];
        $typeId=$cls['type'];
        if(!isset($this->playerLookup[$modelId][$genreId])){
            $this->playerLookup[$modelId][$genreId]=[];
        }
        if(!isset($this->playerLookup[$modelId][$genreId][$proficiencyId])){
            $this->playerLookup[$modelId][$genreId][$proficiencyId]=[];
        }
        if(!isset($this->playerLookup[$modelId][$genreId][$proficiencyId][$ageId])){
            $this->playerLookup[$modelId][$genreId][$proficiencyId][$ageId]=[];
        }
        $this->playerLookup[$modelId][$genreId][$proficiencyId][$ageId][$typeId]=$playerId;
    }
}