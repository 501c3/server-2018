<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/20/18
 * Time: 11:33 PM
 */

namespace App\Doctrine\Iface;


use App\Entity\Competition\Competition;
use App\Entity\Sales\Iface\Participant;
use App\Entity\Sales\Iface\Player;
use App\Entity\Sales\Iface\Qualification;
use App\Repository\Competition\ModelRepository;


class Select
{

    /** @var Classify */
    private $classify;

    private $modelById;

    public function __construct(ModelRepository $modelRepository)
    {
        $this->modelById = $modelRepository->getModelById();
    }



    public function setClassify(Classify $classify)
    {
        $this->classify=$classify;
        return $this;
    }

    public function setCompetition(Competition $competition):Select
    {
        $this->classify->setCompetition($competition);
        return $this;
    }

    public function couple(Participant $p1, Participant $p2) : Player
    {
        /** @var Player $player */
        $player=$this->classify->couple($p1,$p2);
        /** @var Qualification $qualification */
        foreach($player->getAllQualifications() as $qualification){
            $description=$qualification->toArray(Qualification::DOMAIN_NAME_TO_VALUE_ID);
        }
    }

    public function solo(Participant $p) : array
    {
        $player=$this->classify->solo($p);
    }

}