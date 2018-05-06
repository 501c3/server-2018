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

class YamlToSalesException extends \Exception
{
    const INVALID_TOP_KEY=2001;
    const LOGO_NOT_FOUND=2004;
    const START_EXPECTED=2502;
    const FINISH_EXPECTED=2504;
    const DATE_INVALID=2506;
    const INVALID_EMAIL=2604;
    const PARTICIPANT_TAG=2702;
    const INVALID_TAG=2704;
    const INVENTORY_REDUNDANT=2706;
    const NOT_NUMBER=2708;
    const REDUNDANT_DATE=2710;
    const PROCESSOR_INVALID=2802;
    const PROD_TEST=2804;
    const PRICE_INVALID=2806;
    const PRICE_DATE_INVALID=2808;

   public function __construct(string $noted, string $detail, $location, $code, $extraLocation=null)
   {
       $pos=[];
       preg_match('/R(?P<row>\d+)C(?P<col>\d+)/',$location, $pos);
       $message = $extraLocation?null:sprintf('%s at row:%d, col:%d %s', $noted, $pos['row'],$pos['col'], $detail);
       if($extraLocation){
           $xtraPos=[];
           preg_match('/R(?P<row>\d+)C(?P<col>\d+)/',$extraLocation, $xtraPos);
           $message = sprintf('%s between row:%d & row:%d. %s', $noted, $pos['row']-1,$xtraPos['row']+1, $detail);
       }

       parent::__construct( $message, $code, null );
   }
}