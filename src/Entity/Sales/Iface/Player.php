<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/21/18
 * Time: 5:33 PM
 */

namespace App\Entity\Sales\Iface;
use App\Entity\Competition\Model;
use App\Entity\Models\Value;
use Doctrine\Common\Collections\ArrayCollection;

class Player
{
    private $id;

    /** @var ArrayCollection  */
    private $qualifications ;

    /** @var ArrayCollection  */
    private $participants ;

    /** @var ArrayCollection  */
    private $models;


    /**
     * @var ArrayCollection
     *
     * $this->events[$modelId][$genreId]: array
     */
    private $events;

    /**
     * @var ArrayCollection
     *
     */
    private $genres;


    /** @var array  */
    private $dataCache = [];

    public function __construct()
    {
        $this->qualifications=new ArrayCollection(); //of Value
        $this->participants = new ArrayCollection(); //of Participant
        $this->models = new ArrayCollection(); //of Model
        $this->events = new ArrayCollection(); // JSON
        $this->genres = new ArrayCollection();
    }



    public function setId(int $id)
    {
        $this->id=$id;
    }

    public function getId()
    {
        return $this->id;
    }


    public function addQualification(Model $model, Qualification $qualification)
    {

        if(!$this->models->contains($model)){
            $this->models->set($model->getId(),$model);
            if(!isset($this->dataCache['models'])){
                $this->dataCache['models']=[];
            }
            $this->dataCache['models'][$model->getId()]=$model->getName();
        }
        $modelQualifications = $this->qualifications->get($model->getId());
        if(!$modelQualifications) {
            $modelQualifications=new ArrayCollection();
            $this->qualifications->set($model->getId(),$modelQualifications);
        }
        $genreValue=$qualification->get('genre');
        $modelQualifications->set($genreValue->getId(), $qualification);
        $this->qualifications->set($model->getId(),$modelQualifications);
        if(!isset($this->dataCache['qualifications'])) {
            $this->dataCache['qualifications']=[];
        }
        if(!isset($this->dataCache['qualifications'][$model->getId()])){
            $this->dataCache['qualifications'][$model->getId()]=[];
        }
        $this->dataCache['qualifications'][$model->getId()][$genreValue->getId()]
            =$qualification->toArray(Qualification::DOMAIN_NAME_TO_VALUE_ID);
        return $this;
    }


    public function getQualification(Model $model, Value $genre): Qualification
    {
        $collection=$this->qualifications->get($model->getId());
        /** @var Qualification $qualification */
        $qualification=$collection->get($genre->getId());
        return $qualification;
    }

    public function getModelGenreKeys()
    {
        $modelKeys = $this->qualifications->getKeys();
        $modelGenreKeys=[];
        foreach($modelKeys as $key){
            $collection=$this->qualifications->get($key);
            $modelGenreKeys[$key]=$collection->getKeys();
        }
        return $modelGenreKeys;
    }

    /**
     * @param int $model
     * @param int $genre
     * @return mixed|null
     */
    public function getQualificationByKeys(int $model, int $genre)
    {
        /** @var ArrayCollection $collection */
        $collection=$this->qualifications->get($model);
        $qualification=$collection->get($genre);
        return $qualification;

    }


    public function addParticipant(Participant $p): Player
    {
        $this->participants->set($p->getId(),$p);
        if(!isset($this->dataCache['participants'])) {
            $this->dataCache['participants'] = [];
        }
        array_push($this->dataCache['participants'],$p->getId());
        return $this;
    }

    public function getParticipant(int $id): Participant
    {
        return $this->participants->get($id);
    }

    public function participantCount()
    {
        return $this->participants->count();
    }

    public function getModelChoiceNames()
    {
        $participantKeys = $this->participants->getKeys();
        $modelKeys = [];
        foreach($participantKeys as $participantKey) {
            /** @var Participant $participant */
            $participant=$this->participants->get($participantKey);
            $keys=$participant->fetchModelKeys();
            $modelKeys[]=$keys;
        }
        $modelNames= count($modelKeys)>1?array_intersect(...$modelKeys):$modelKeys[0];
        return $modelNames;
    }

    public function addEvents(Model $model, array $event)
    {
        /** @var ArrayCollection $modelEvents */

        $modelEvents = $this->events->get($model->getId());
        if(!$modelEvents){
            $modelEvents = new ArrayCollection();
        }
        $modelEvents->add($event);
        $this->events->set($model->getId(),$modelEvents);
        if(!isset($this->dataCache['events'])) {
            $this->dataCache['events']=[];
        }
        if(!isset($this->dataCache['events'][$model->getId()])) {
            $this->dataCache['events'][$model->getId()]=[];
        }
        $this->dataCache['events'][$model->getId()][$event['id']]=$event;
        return $this;
    }


   /**
     * @return mixed
     */
    public function getParticipantIds(): array
    {
        return $this->dataCache['participants'];
    }


    /**
     * @return mixed
     */
    public function getEvents() : array
    {
        return isset($this->dataCache['events'])?$this->dataCache['events']:[];
    }

    /**
     * @return mixed
     */
    public function getSelections() : array
    {
        return isset($this->dataCache['selections'])?$this->dataCache['selections']:[];
    }

    public function setSelections(array $selections):Player
    {
        $this->dataCache['selections']=$selections;
        return $this;
    }

    public function getExclusions() : array
    {
        return isset($this->dataCache['exclusions'])?$this->dataCache['exclusions']:[];
    }

    // TODO: Set exclusions in repository from  summary module.
    public function setExclusions(array $exclusions) : Player
    {
        $this->dataCache['exclusions']= $exclusions;
        return $this;
    }

    /**
     * $player has taken events that $this can not enter because of common teacher
     * @param Player $player
     * @return Player
     */
    /*public function updateExclusions(Player &$player) : Player
    {
        if($player === $this) return $this;
        $localParticipantIds = array_values(array_intersect($player->getParticipantIds(),
                                                            $this->getParticipantIds()));
        if(!count($localParticipantIds)) return $this;
        $remoteParticipantIds= array_values(array_diff($player->getParticipantIds(),
                                                       $localParticipantIds));
        $localParticipant=$this->participants->get($localParticipantIds[0]);
        $remoteParticipant=$player->participants->get($remoteParticipantIds[0]);
        $localChoices = $this->getEvents();
        $remoteSelections = $player->getSelections();
        $exclusions = &$this->dataCache['exclusions'];
        foreach($localChoices as $modelId=>$localEvents){
            $localEventIds = array_keys($localEvents);
            if(isset($remoteSelections[$modelId])){
                $remoteEventIds = $remoteSelections[$modelId];
                if(!isset($exclusions[$modelId])) {
                    $exclusions[$modelId]=[];
                }
                $unavailableEventIds=array_intersect($localEventIds,$remoteEventIds);
                foreach($unavailableEventIds as $id) {
                    $exclusions[$modelId][$id]=['local'=>$localParticipant->getName(),
                                                'remote'=>$remoteParticipant->getName()];
                }
            }
        }
        return $this;
    }*/




    public function updateMultipleExclusions(array $players) : Player
    {
        /** @var Player $player */
        foreach($players as $player){
            $this->updateExclusions($player);
        }
        return $this;
    }

    public function toArray()
    {
        return $this->dataCache;
    }

    public function preJSON()
    {

    }

    public function describe()
    {
        $data = ['participants'=>[]];
        /** @var \ArrayIterator $iterator */
        $iterator=$this->participants->getIterator();
        /** @var Participant $participant */
        while($participant=$iterator->current()) {
            $data['participants'][$participant->getId()] = $participant->getName();
            $iterator->next();
        }
        $iteratorModel = $this->qualifications->getIterator();
        $model=$this->models->get($iteratorModel->key());
        $modelName = $model->getName();
        $modelQualifications = [];
        /** @var  ArrayCollection $arrayCollectionModel */
        while($arrayCollectionModel=$iteratorModel->current()){
            $iteratorQualification = $arrayCollectionModel->getIterator();
            if(!isset($modelQualifications[$modelName])) {
                $modelQualifications[$modelName]=[];
            }
            /** @var Qualification $qualification */
            while($qualification = $iteratorQualification->current()) {
                $genreValue=$qualification->get('genre');
                $modelQualifications[$modelName][$genreValue->getName()]
                    =$qualification->toArray(Qualification::DOMAIN_NAME_TO_VALUE_NAME);
                $iteratorQualification->next();
            }
            $iteratorModel->next();
        }
        $data['qualifications']= $modelQualifications;
        $data['models']     = isset($this->dataCache['models'])?$this->dataCache['models']:[];
        $data['events']     = isset($this->dataCache['events'])?$this->dataCache['events']:[];
        $data['selections'] = isset($this->dataCache['selections'])?$this->dataCache['selections']:[];
        return $data;
    }

}