<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/1/18
 * Time: 11:54 AM
 */

namespace App\Doctrine\Iface;


class ClassifyException extends \Exception
{
  const CODE = 8000 ;

  public function __construct(string $name, string $reason, int $code = 0)
  {
      $message = sprintf('Unable to classify %s. Reason: %s', $name,$reason);
      parent::__construct( $message, self::CODE, null);
  }
}