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

class Team
{
    private $participants;

    private $qualifications;

    public function __construct()
    {
        $this->qualifications = new ArrayCollection();
    }

    public function addParticipant(Participant $p)
    {

    }


}