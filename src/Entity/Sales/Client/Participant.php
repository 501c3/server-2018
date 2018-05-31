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

    /** @var Value */
    private $type;

    /** @var string */
    private $status;

    /** @var ArrayCollection|null*/
    private $genreProficiency;

    /** @var ArrayCollection|null */
    private $models;

    /** @var array|null  */
    private $domainValueHash;

    /**
     * Classify constructor.
     * @param array|null $domainValueHash
     */
    public function __construct(array $domainValueHash=null)
    {
        $this->domainValueHash = $domainValueHash;
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
    public function getType(): Value
    {
        return $this->type;
    }

    private function buildException(int $foundId, Value $value)
    {
        $person = $this->first.' '.$this->last;
        $name = $value->getName();
        $actualId = $value->getId();
        return new ParticipantCheckException("For $person found $foundId but $actualId corresponds to $name,",9000);
    }

    /**
     * @param int $type
     * @param Value|null $value
     * @return Participant
     * @throws ParticipantCheckException
     */
    public function setType(int $type, Value $value=null): Participant
    {
        if($value) {
            if($value->getId()!=$type) {
                throw $this->buildException($type,$value);
            }
            $this->type = $value;
        }
        $this->type=$type;
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
     * @param Value|null $genreValue
     * @param Value|null $proficiencyValue
     * @return Participant
     * @throws ParticipantCheckException
     */
    public function addGenreProficiency(int $genre,int $proficiency,
                                        Value $genreValue=null, Value $proficiencyValue=null) : Participant
    {
        $this->genreProficiency[$genre] = $proficiency;
        if($genreValue) {
            if(!isset($this->genreProficiency[$genreValue->getId()])){
                throw $this->buildException($genre,$genreValue);
            }
        }
        if($proficiencyValue){
            if($this->genreProficiency[$genreValue->getId()]!=$proficiencyValue->getId()){
                throw $this->buildException($proficiency,$proficiencyValue);
            }
        }
        return $this;
    }

    public function getGenreProficiency()
    {
        return $this->genreProficiency;
    }

    /**
     * @param int $modelId
     * @param Model|null $model
     * @return Participant
     * @throws ParticipantCheckException
     */
    public function addModel(int $modelId, Model $model=null): Participant
    {
        if($model) {
            if($modelId != $model->getId()) {
                $person = $this->first.' '.$this->last;
                $name = $model->getName();
                $actualId = $model->getId();
                throw new ParticipantCheckException(
                    "For $person found $modelId but $actualId corresponds to $name,",9000);
            }
        }
        $this->models->set($modelId,$model);
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