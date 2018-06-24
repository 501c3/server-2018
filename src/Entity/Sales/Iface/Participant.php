<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/21/18
 * Time: 3:42 PM
 */

namespace App\Entity\Sales\Iface;

use App\Entity\Competition\Model;
use App\Entity\Models\Value;
use Doctrine\Common\Collections\ArrayCollection;

class Participant
{
    /** @var int */
    private $id;

    /** @var string */
    private $first;

    /** @var string */
    private $last;

    /** @var integer */
    private $years;

    /** @var string */
    private $sex;

    /**
     * @var Value        var_dump($values);die;
     * Professional or Amateur
     */
    private $typeA;

    /**
     * @var Value
     * Teacher or Student
     */
    private $typeB;

    /** @var string */
    private $status;

    private $genres;

    private $genreProficiency;

    private $models;

    public function __construct()
    {
        $this->genres = new ArrayCollection();
        $this->genreProficiency = new ArrayCollection();
        $this->models = new ArrayCollection();
    }


    /**
     * @return int
     */
    public function getId():?int
    {
        return $this->id;
    }

    public function setId(int $id): Participant
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirst(): string
    {
        return $this->first;
    }

    /**
     * @param string $first
     * @return Participant
     */
    public function setFirst(string $first): Participant
    {
        $this->first = $first;
        return $this;
    }

    /**
     * @return string
     */
    public function getLast(): string
    {
        return $this->last;
    }

    /**
     * @param string $last
     * @return Participant
     */
    public function setLast(string $last): Participant
    {
        $this->last = $last;
        return $this;
    }

    public function getName() : string
    {
        return $this->first.' '.$this->last;
    }

    /**
     * @return int
     */
    public function getYears(): ?int
    {
        return $this->years;
    }

    /**
     * @param int $years
     * @return Participant
     */
    public function setYears(int $years): Participant
    {
        $this->years = $years;
        return $this;
    }

    /**
     * @return string
     */
    public function getSex(): string
    {
        return $this->sex;
    }

    /**
     * @param string $sex
     * @return Participant
     */
    public function setSex(string $sex): Participant
    {
        $this->sex = $sex;
        return $this;
    }

    /**
     * @return Value
     */
    public function getTypeA(): Value
    {
        return $this->typeA;
    }

    /**
     * @return Value
     */
    public function getTypeB(): Value
    {
        return $this->typeB;
    }



    /**
     * @param Value $type
     * @return Participant
     */
    public function setTypeA(Value $type): Participant
    {
        $this->typeA=$type;
        return $this;
    }

    /**
     * @param Value $type
     * @return Participant
     */
    public function setTypeB(Value $type): Participant
    {
        $this->typeB=$type;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Participant
     */
    public function setStatus(string $status): Participant
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param Value $genre
     * @param Value $proficiency
     * @return Participant

     */
    public function addGenreProficiency(Value $genre,Value $proficiency) : Participant
    {
        $this->genres->set($genre->getName(),$genre);
        $this->genreProficiency->set($genre->getName(),$proficiency);
        return $this;
    }

    public function addModel(Model $model): Participant
    {
        $this->models->set($model->getName(),$model);
        return $this;
    }

    public function fetchModelKeys():array
    {
        return $this->models->getKeys();
    }

    public function fetchModelIds():array
    {
        $values = $this->models->getValues();
        return $values;
    }

    public function fetchGenreProficiency(Value $genre)
    {
        return $this->genreProficiency->get($genre->getName());
    }

    public function fetchGenreNames() {
        return $this->genreProficiency->getKeys();
    }

    private function getGenreProficiency(bool $toClient)
    {
        $genreProficiency = [];
        /** @var \ArrayIterator $iter */
        $iterator=$this->genres->getIterator();
        /** @var Value $genreValue */
        while($genreValue=$iterator->current()) {
            $genreName = $iterator->key();
            /** @var Value $proficiencyValue */
            $proficiencyValue = $this->genreProficiency->get($genreName);
            $genreProficiency[$toClient?$genreValue->getId():$genreValue->getName()]
                =$toClient?$proficiencyValue->getId():$proficiencyValue->getName();
            $iterator->next();
        }
        return $genreProficiency;
    }


    public function getModels()
    {
        return $this->models;
    }

    public function getModelIds(bool $toArray)
    {
        $models = [];
        $iterator = $this->models->getIterator();
        while($model=$iterator->current()) {
            $modelName = $iterator->key();
            /** @var Model $modelObject */
            $modelObject = $this->models->get($modelName);
            $models[]= $toArray?$modelObject->getId():$modelObject->getName();
            $iterator->next();
        }
        return $models;
    }


    public function hasId()
    {
        return $this->id?true:false;
    }

    public function toArray()
    {

        return ['first'=>$this->first,
                'last'=>$this->last,
                'sex'=>$this->sex,
                'years'=>$this->years,
                'typeA'=>$this->typeA->getId(),
                'typeB'=>$this->typeB->getId(),
                'models'=>$this->getModelIds(true),
                'genreProficiency'=>$this->getGenreProficiency(true)];

    }

    public function describe() {
        return ['id'=>$this->id,
                'first'=>$this->first,
                'last'=>$this->last,
                'sex'=>$this->sex,
                'years'=>$this->years,
                'typeA'=>$this->typeA->getName(),
                'typeB'=>$this->typeB->getName(),
                'models'=>$this->getModelIds(false),
                'genreProficiency'=>$this->getGenreProficiency(false)];
    }
}


