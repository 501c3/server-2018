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

namespace App\Entity\Sales\Client;


use Doctrine\Common\Collections\ArrayCollection;

class Player
{
    private $participants=[];

    private $qualifications;

    public function __construct()
    {
        $this->qualifications = new ArrayCollection();
    }

    public function addParticipant(Participant $p)
    {
        array_push($this->participants, $p);
    }

    public function addQualification(Qualification $qualification)
    {
        /** @var Value $value */
       $value = $qualification->get('genre');
       if(!$value) {
           throw new ClientException('Player Generation', "No qualification was defined.", 9000);
       }
        $this->qualifications->set($value->getName(),$qualification);
        return $this;
    }

    public function setQualifications(array $qualificationList):Player
    {
        foreach($qualificationList as $qualification) {
            $this->addQualification($qualification);
        }
        return $this;
    }

    public function getQualification(string $genre): Qualification
    {
        return $this->qualifications->get($genre);
    }

    public function getAllQualifications(): ArrayCollection
    {
        return $this->qualifications;
    }


    public function getGenres():array
    {
        return $this->qualifications->getKeys();
    }

}