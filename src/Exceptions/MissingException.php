<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/28/18
 * Time: 1:38 PM
 */

namespace App\Exceptions;


use Throwable;

class MissingException extends \Exception
{
    public function __construct(array $missing, array $locations, int $code)
    {

        if (count( $locations ) > 0) {
            $first = array_shift( $locations );
            array_unshift( $locations, $first );
            $last = array_pop( $locations );
            $top = [];
            $bottom = [];
            preg_match( '/R(?P<row>\d+)C(?P<col>\d+)/', $first, $top );
            preg_match( '/R(?P<row>\d+)C(?P<col>\d+)/', $last, $bottom );
            $message = sprintf( 'Missing "%s" domain definitions between lines %d and %d.', join( '","', $missing ), $top['row'], $bottom['row'] );
            parent::__construct( $message, $code, null );
        }else {
            $message = "Missing domain definition section";
            parent::__construct( $message, $code, null );
        }

    }
}