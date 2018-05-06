<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/3/18
 * Time: 10:16 AM
 */

namespace App\Exceptions;


use Throwable;

class RebuildException extends \Exception
{
    const NO_PRIMITIVES = 3002;
    const NO_MODELS = 3004;
    public function __construct(string $message = "", int $code = 0)
    {
        parent::__construct( $message, $code, null );

    }
}