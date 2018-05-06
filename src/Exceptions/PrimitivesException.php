<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/27/18
 * Time: 10:00 AM
 */

namespace App\Exceptions;

class PrimitivesException extends \Exception
{
    public function __construct(string $found, string $location, string $detail, int $code)
    {
        $pos=[];
        preg_match('/R(?P<row>\d+)C(?P<col>\d+)/',$location, $pos);
        $message = sprintf('"%s" at row:%d, col:%d %s', $found, $pos['row'],$pos['col'], $detail);
        parent::__construct($message,$code,null);
    }
}