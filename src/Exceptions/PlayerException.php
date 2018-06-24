<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/23/18
 * Time: 7:09 AM
 */

namespace App\Exceptions;

use App\Entity\Competition\Model;
use App\Entity\Sales\Iface\Participant;

class PlayerException extends \Exception
{
    /**
     * @var Participant
     */
    private $p1;
    /**
     * @var Participant
     */
    private $p2;



    public function __construct(string  $message, int $code, Participant $p1,Participant $p2=null)
    {

        parent::__construct( $message, $code, null);
        $this->p1 = $p1;
        $this->p2 = $p2;
    }

    public function getParticipantCount():int
    {
        return $this->p1 && $this->p2?2:$this->p1?1:0;
    }

    public function getParticipant(int $number):?Participant
    {
        switch($number){
            case 1:
                return $this->p1;
            case 2:
                return $this->p2;
            default:
                return null;
        }
    }
}