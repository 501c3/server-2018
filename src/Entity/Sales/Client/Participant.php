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


use App\Entity\Models\Value;
use App\Exceptions\ParticipantCheckException;
use Doctrine\Common\Collections\ArrayCollection;

class Participant
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

    private $styleProficiency=[];



    public function __construct()
    {
        $this->styleProficiency = new ArrayCollection();
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

    /**
     * @param Value $type
     * @return Participant
     */
    public function setType(Value $type): Participant
    {
        $this->type = $type;
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

    public function addStyleProficiency(int $genre,int $proficiency, Value $genreValue=null, Value $proficiencyValue=null)
    {
        $this->styleProficiency[$genre] = $proficiency;
        if($genreValue) {
            if(!isset($this->styleProficiency[$genreValue->getId()])){
                $name = $genreValue->getName();
                throw new ParticipantCheckException('genreId does not check.',9000);
            }
        }
        if($proficiencyValue){
            if($this->styleProficiency[$genreValue->getId()]!=$proficiencyValue->getId()){
                throw new ParticipantCheckException('Style proficiencyId for '.
                    $genreValue->getName().' does not check.',9000);
            }
        }
    }
}