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

namespace App\Entity\Sales\Client;

use App\Entity\Competition\Model;
use App\Entity\Models\Value;
use App\Exceptions\ParticipantCheckException;
use Doctrine\Common\Collections\ArrayCollection;

class Participant implements \Serializable
{
    /** @var string */
    private $first;

    /** @var string */
    private $last;

    /** @var integer */
    private $years;

    /** @var string */
    private $sex;

    /** @var int|Value */
    private $typeA;

    /** @var int|Value */
    private $typeB;

    /** @var string */
    private $status;

    /** @var ArrayCollection|null*/
    private $genreProficiency;

    /** @var array|null  */
    private $valueById;

    /**@var array*/
    private $modelById;

    private $model=[];


    /**
     * Participant constructor.
     * @param array $valueById
     */
    public function __construct(array $valueById, array $modelById)
    {
        $this->valueById = $valueById;
        $this->modelById = $modelById;
        $this->genreProficiency = new ArrayCollection();
        $this->models = new ArrayCollection();

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
    public function getYears(): int
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
        return intval($this->typeA)?$this->valueById[$this->typeA]:$this->typeA;
    }

    /**
     * @return Value
     */
    public function getTypeB(): Value
    {
        return intval($this->typeB)?$this->valueById[$this->typeB]:$this->typeB;
    }



    /**
     * @param int|Value $type
     * @return Participant
     */
    public function setTypeA($type): Participant
    {
        $this->typeA=$type;
        return $this;
    }

    /**
     * @param int|Value $type
     * @return Participant
     */
    public function setTypeB($type): Participant
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
     * @param int $genre
     * @param int $proficiency
     * @return Participant

     */
    public function addGenreProficiency(int $genre,int $proficiency) : Participant
    {
        $this->genreProficiency[$genre] = $proficiency;
        return $this;
    }

    public function getGenreProficiency($genreId=null)
    {
        if($genreId) {
            return $this->genreProficiency[$genreId];
        }
        return $this->genreProficiency;
    }

    /**
     * @param int $modelId
     * @param Model|null $model
     * @return Participant
     * @throws ParticipantCheckException
     */
    public function addModel(int $modelId): Participant
    {
        array_push($this->model, $modelId);
        return $this;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        $str=  json_encode(['first'=>$this->first,
                            'last' => $this->last,
                            'sex'=>$this->sex,
                            'genreProficiency'=>$this->genreProficiency->toArray(),
                            'models'=> $this->models->toArray()]);
        return $str;
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $arr=json_decode($serialized);
        $this->first=$arr['first'];
        $this->last=$arr['last'];
        $this->years=intval($arr['years']);
        $this->sex=$arr['sex'];
        $this->genreProficiency = new ArrayCollection($arr['genreProficiency']);
        $this->models = new ArrayCollection($arr['models']);
    }
}