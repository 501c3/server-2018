<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/20/18
 * Time: 11:23 PM
 */

namespace App\Tests\Doctrine\Iface;

use App\Doctrine\Builder;
use App\Entity\Competition\Competition;
use App\Entity\Models\Value;
use App\Exceptions\GeneralException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;
use App\Utils\YamlPosition;

class CompetitionSalesParser extends Builder
{
    private $domainValueHash = [];
    /**
     * @var CompetitionRepository
     */
    private $competitionRepository;
    /**
     * @var ModelRepository
     */
    private $modelRepository;
    /**
     * @var ValueRepository
     */
    private $valueRepository;

    /** @var Competition */
    private $competition;

    private $models = [];

    public function __construct(
        CompetitionRepository $competitionRepository,
        ModelRepository $modelRepository,
        ValueRepository $valueRepository)
    {
        $this->initialize($valueRepository);
        $this->competitionRepository = $competitionRepository;
        $this->modelRepository = $modelRepository;
        $this->valueRepository = $valueRepository;
    }


    public function parse($yamlText)
    {
        $r = YamlPosition::parse($yamlText);
        $lineCount=YamlPosition::getLineCount();
        if(key($r['data'])=='comment'){
            next($r['data']); next($r['position']);
        }
        list($competitionName, $competitionNamePosition, $competitionKey, $competitionKeyPosition)
            = $this->current($r['data'],$r['position']);
        $competition=$this->fetchCompetition($competitionName, $competitionNamePosition,
                                             $competitionKey, $competitionKeyPosition);
        list($modelNames, $modelNamesPosition, $modelKey, $modelKeyPosition)
            = $this->next($r['data'],$r['position']);
        $models = $this->fetchModels($modelNames, $modelNamesPosition, $modelKey, $modelKeyPosition);
        list($submissions, $submissionsPosition, submissionKey, $submissionKeyPosition)
            = $this->next($r['data'], $r['position']);
        $submissions = $this->fetchSubmissions($submissions, $submissionsPosition, $submissionKey, $submissionKeyPOsition);

    }




    private function fetchSubmissions($data,$positions, $key, $keyPosition)
    {
        if($key != 'submissions') {
            throw new GeneralException($key, $keyPosition, 'expected "models"',
                ExceptionCode::SUBMISSIONS);
        }
        $submissions = [];
        list($submissionData, $submissionDataPosition, , )
            = $this->current($data,$position);
        while($submissionData){
            $submission = $this->buildSubmission($submissionData,$submissionDataPosition);
            array_push($submissions,$submission);
            list($submissionData,$submissionDataPosition, , )
                = $this->next($data,$position);
        }
        return $submissions;
    }

    private function buildSubmissions($data,$position)
    {
        var_dump($data);die;
    }
}