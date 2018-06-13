<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/9/18
 * Time: 11:52 AM
 */

namespace App\Tests\Doctrine\Iface;


class EntriesAssessExceptionCode
{
    const CHANNEL = 4002;
    const INVALID_CHANNEL = 4004;
    const PARTICIPATION = 4202;
    const PARTICIPATION_KEYS = 4212;
    const MISSING_PARTICIPATION_KEYS = 4214;
    const KEY_FIRST = 4216;
    const KEY_LAST  = 4218;
    const PROFICIENCY = 4220;
    const AGE = 4222;
    const TYPE = 4224;
    const INVALID_PROFICIENCY = 4226;
    const INVALID_AGE = 4228;
    const INVALID_TYPE = 4230;
    const PROFICIENCIES = 4232;
    const AGES = 4234;
    const INVALID_MODEL = 4240;
    const STYLE = 4242;
    const TYPE_EVENT = 4244;
    const TAG = 4246;
    const CHOSEN = 4248;
    const ASSESS = 4250;
    const INVALID_STYLE = 4260;
    const INVALID_TYPE_EVENT = 4262;
    const INVALID_TAG = 4264;
    const INVALID_CHOSEN = 4266;
    const INVALID_ASSESS = 4268;
}