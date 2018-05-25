<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/23/18
 * Time: 9:05 AM
 */

namespace App\Tests\Doctrine\Iface;


use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;

class ParticipantClassifyParser extends BaseParser
{
    public function __construct(
        CompetitionRepository $competitionRepository,
        ModelRepository $modelRepository,
        ValueRepository $valueRepository
    )
    {
        parent::__construct($competitionRepository,
                            $modelRepository,
                            $valueRepository);
    }

    public function parse(string $yaml)
    {
        $r = $this->fetchPhpArray($yaml);
        if(key($r['data'])=='comment'){
            next($r['data']); next($r['position']);
        }
        list($competitionName, $competitionNamePosition, $competitionKey, $competitionKeyPosition)
            = $this->current($r['data'],$r['position']);
        $this->fetchCompetition($competitionName, $competitionNamePosition,
                                             $competitionKey, $competitionKeyPosition);
        $this->fetchModels(next($r['data']), next($r['position']), key($r['data']), key($r['position']));
    }

}