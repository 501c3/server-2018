<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/23/18
 * Time: 8:31 AM
 */

namespace App\Doctrine\Iface;

use App\Entity\Competition\Competition;
use App\Entity\Competition\Model;
use App\Entity\Sales\Client\Participant;
use Doctrine\Common\Collections\ArrayCollection;

class ParticipantClassify
{
    private $participants;
    /**
     * @var Competition
     */
    private $competition;

    public function __construct(
        Competition $competition
    )
    {
        $this->participants = new ArrayCollection();

        $this->competition = $competition;
    }

    public function add(Participant $p)
    {
        $this->participants->add($p);
    }

    public function player()
    {
        if($this->participants->count())
        switch($this->participants->count) {
            case 0:
                //TODO: Throw exception
                break;
            case 1:
                //TODO: Classify Solo
                break;
            case 2:
                //TODO: Classify Couple
                break;
            default:
                break;
        }
    }

    public function clear()
    {
        $this->$this->participants->clear();
    }
}