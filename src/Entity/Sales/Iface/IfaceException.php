<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/3/18
 * Time: 4:22 PM
 */

namespace App\Entity\Sales\Iface;


use Throwable;

class IfaceException extends \Exception
{
    public function __construct(string $name , string $message, int $code = 0)
    {
        $text = "$name: $message";
        parent::__construct( $text, $code);
    }

}