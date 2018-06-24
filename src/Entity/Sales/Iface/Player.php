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

    public function addEvents(Model $model, array $events)
    {
        /** @var ArrayCollection $modelEvents */
        $modelEvents = $this->events->get($model->getId());
        if(!$modelEvents){
            $modelEvents = new ArrayCollection();
        }
        $modelEvents->add($events);
        $this->events->set($model->getId(),$modelEvents);
        if(!isset($this->dataCache['events'])) {
            $this->dataCache['events']=[];
        }
        if(!isset($this->dataCache['events'][$model->getId()])) {
            $this->dataCache['events'][$model->getId()]=[];
        }
        array_push($this->dataCache['events'][$model->getId()],$events);
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

    /**
     * @return mixed
     */
    public function getExclusions() : array
    {
        return isset($this->dataCache['exclusions'])?$this->dataCache['exclusions']:[];
    }

    public function setExclusions($exclusions) : Player
    {
        $this->dataCache['exclusions']=$exclusions;
    }


    public function setAssessment(\DateTime $date, float $amount) {
        $this->dataCache['assessment'] = $amount;
        $this->dataCache['assessment-date'] = $date->format('Y-m-d H:i:s');
    }

    public function addPayment(\DateTime $date, float $payment)
    {
        if(!isset($this->dataCache['payments'])) {
            $this->dataCache['payments'] = [];
        }
        $this->dataCache['payments'][$date->format('Y-m-d H:i:s')]=$payment;
    }

    public function toArray()
    {
        return $this->dataCache;
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

        $modelQualifications = [];
        /** @var  ArrayCollection $arrayCollectionModel */
        while($arrayCollectionModel=$iteratorModel->current()){
            $iteratorQualification = $arrayCollectionModel->getIterator();
            if(!isset($modelQualifications[$iteratorModel->key()])) {
                $modelQualifications[$iteratorModel->key()]=[];
            }
            /** @var Qualification $qualification */
            while($qualification = $iteratorQualification->current()) {
                $genreValue=$qualification->get('genre');
                $modelQualifications[$iteratorModel->key()][$genreValue->getName()]
                    =$qualification->toArray(Qualification::DOMAIN_NAME_TO_VALUE_NAME);
                $iteratorQualification->next();
            }
            $iteratorModel->next();
        }
        $data['qualifications']= $modelQualifications;
        $data['models']     = isset($this->dataCache['models'])?$this->dataCache['models']:[];
        $data['events']     = isset($this->dataCache['events'])?$this->dataCache['events']:[];
        $data['selections'] = isset($this->dataCache['selections'])?$this->dataCache['selections']:[];
        $data['exclusions'] = isset($this->dataCache['exclusions'])?$this->dataCache['exclusions']:[];
        $data['assessment'] = isset($this->dataCache['assessment'])?$this->dataCache['assessment']:[];
        return $data;
    }

}