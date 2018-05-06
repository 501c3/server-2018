<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/25/18
 * Time: 6:21 PM
 */

namespace App\Exceptions;

class YamlToSalesMissingException extends \Exception
{
   const MISSING_KEY = 2002;

   public function __construct(string $key, $code)
   {
      $message = sprintf("Missing \"$key\".");
       parent::__construct( $message, $code, null );
   }
}