<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/18/18
 * Time: 5:38 PM
 */

namespace App\Entity\Sales\Iface;


class PlayerList
{
    private $byGent;
    private $byLady;
    private $byTeacher;
    private $byStudent;

    public function __construct()
    {
    }
}