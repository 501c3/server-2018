<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/20/18
 * Time: 8:32 AM
 */

namespace App\Entity\Sales\Iface;



use App\Entity\Sales\Tag;

abstract class Classify
{
    /** @var array */
    protected $domainValueHash;

    /** @var array */
    protected $valueById;

    /** @var array */
    protected $modelById;

    /** @var Tag */
    protected $playerTag;

    /** @var Tag */
    protected $participantTag;

    /** @var array */
    protected $ageMapping;

    /** @var array */
    protected $proficiencyMapping;

    public abstract function couple(Participant $p1, Participant $p2);
    public abstract function solo(Participant $p);


    public function setDomainValueHash(array $domainValueHash): Classify
    {
        $this->domainValueHash = $domainValueHash;
        return $this;
    }

    public function setValueById(array $valueById): Classify
    {
        $this->valueById = $valueById;
        return $this;
    }

    public function setModelById(array $modelById): Classify
    {
        $this->modelById = $modelById;
        return $this;
    }

    public function setPlayerTag(Tag $tag): Classify
    {
        $this->playerTag = $tag;
        return $this;
    }

    public function setParticipantTag(Tag $tag): Classify
    {
        $this->participantTag = $tag;
        return $this;
    }

    public function setProficiencyMapping(array $mapping): Classify
    {
        $this->proficiencyMapping=$mapping;
        return $this;
    }

    public function setAgeMapping(array $mapping): Classify
    {
        $this->ageMapping = $mapping;
        return $this;
    }



    public function describe() {

    }

}