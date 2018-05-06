<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/13/18
 * Time: 1:08 AM
 */

namespace App\Exceptions;


class RedundanceException extends \Exception
{
    public function __construct(string $noted, $previousLines, $currentLines, $code)
    {
        $message = "$noted, previous lines: $previousLines, current lines: $currentLines.";
        parent::__construct( $message, $code);
    }
}