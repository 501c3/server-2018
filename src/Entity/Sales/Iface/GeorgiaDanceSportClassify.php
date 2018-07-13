<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/23/18
 * Time: 8:31 AM
 */

namespace App\Entity\Sales\Iface;
use App\Entity\Competition\Model;
use App\Entity\Models\Value;
use App\Exceptions\ClassifyException;
use Doctrine\Common\Collections\ArrayCollection;


class GeorgiaDanceSportClassify extends Classify
{

    const AGE_AMATEUR  = [75=>[70=>'Senior 5',
                               60=>'Senior 4',
                               50=>'Senior 3',
                               40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult',
                               17=>'Adult'],
                          65=>[60=>'Senior 4',
                               50=>'Senior 3',
                               40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult',
                               17=>'Adult'],
                          55=>[50=>'Senior 3',
                               40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult',
                               17=>'Adult'],
                          45=>[40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult',
                               17=>'Adult'],
                          35=> [30=>'Senior 1',
                                19=>'Adult'],
                          19=> [14=>'Adult'],
                          16=> [2=>'Youth'],
                          14=> [2=>'Junior 2'],
                          12=> [2=>'Junior 1'],
                          10=> [2=>'Preteen 2'],
                           7=> [2=>'Preteen 1'],
                           5=> [2=>'Juvenile'],
                           2=> [2=>'Baby']];

    const AGE_FUN_EVENT = [49=>[9=>'Senior Youngster'],
                           29=>[9=>'Adult Youngster']];

    const AGE_STUDENT= [75=>'Senior 5',
                        65=>'Senior 4',
                        55=>'Senior 3',
                        45=>'Senior 2',
                        35=>'Senior 1',
                        19=>'Adult',
                        16=>'Youth',
                        14=>'Junior 2',
                        12=>'Junior 1',
                        10=>'Preteen 2',
                        7=>'Preteen 1',
                        5=>'Juvenile',
                        1=>'Baby'];


    const AGE_MEDAL =[50=>'Senior 50',
                      16=>'Adult 16-50',
                      12=>'Junior 12-16',
                      8=>'Under 12',
                      6=>'Under 8',
                      1=>'Under 6'];

    const HIGHER_PROFICIENCY_MEDAL =
        [
            'Pre Bronze'=>['Pre Bronze'=>'Pre Bronze',
                'Bronze'=>'Bronze',
                'Silver'=>'Silver',
                'Gold'=>'Gold'],
            'Bronze'=>[ 'Pre Bronze'=>'Bronze',
                'Bronze'=>'Bronze',
                'Silver'=>'Silver',
                'Gold'=>'Gold'],
            'Silver'=>[ 'Pre Bronze'=>'Silver',
                'Bronze'=>'Silver',
                'Silver'=>'Silver',
                'Gold'=>'Gold'],
            'Gold'=>['Pre Bronze'=>'Gold',
                'Bronze'=>'Gold',
                'Silver'=>'Gold',
                'Gold'=>'Gold']
        ];




    const HIGHER_PROFICIENCY_AMATEUR = [
        'Social' => ['Social'=>'Social'],
        'Newcomer'=>
            [
                'Newcomer'=>'Newcomer',
                'Bronze'=>'Bronze',
                'Silver'=>'Silver',
                'Gold'=>'Gold',
                'Novice'=>'Novice',
                'Pre Championship'=>'Pre Championship',
                'Championship'=>'Championship'
            ],

        'Bronze'=>
            [
                'Newcomer'=>'Bronze',
                'Bronze'=>'Bronze',
                'Silver'=>'Silver',
                'Gold'=>'Gold',
                'Novice'=>'Novice',
                'Pre Championship'=>'Pre Championship',
                'Championship'=>'Championship'
            ],

        'Silver'=>
            [
                'Newcomer'=>'Silver',
                'Bronze'=>'Silver',
                'Silver'=>'Silver',
                'Gold'=>'Gold',
                'Novice'=>'Novice',
                'Pre Championship'=>'Pre Championship',
                'Championship'=>'Championship'
            ],

        'Gold'=>
            [
                'Newcomer'=>'Gold',
                'Bronze'=>'Gold',
                'Silver'=>'Gold',
                'Gold'=>'Gold',
                'Novice'=>'Novice',
                'Pre Championship'=>'Pre Championship',
                'Championship'=>'Championship'
            ],

        'Novice'=>
            [
                'Newcomer'=>'Novice',
                'Bronze'=>'Novice',
                'Silver'=>'Novice',
                'Gold'=>'Novice',
                'Novice'=>'Novice',
                'Pre Championship'=>'Pre Championship',
                'Championship'=>'Championship'
            ],

        'Pre Championship'=>
            [
                'Newcomer'=>'Pre Championship',
                'Bronze'=>'Pre Championship',
                'Silver'=>'Pre Championship',
                'Gold'=>'Pre Championship',
                'Novice'=>'Pre Championship',
                'Pre Championship'=>'Pre Championship',
                'Championship'=>'Championship'
            ],
        'Championship'=>
            [
                'Newcomer'=>'Championship',
                'Bronze'=>'Championship',
                'Silver'=>'Championship',
                'Gold'=>'Championship',
                'Novice'=>'Championship',
                'Pre Championship'=>'Championship',
                'Championship'=>'Championship'],
            ];

    const HIGHER_PROFICIENCY_STUDENT = [
        'Newcomer'=>
            [
                'Newcomer'=>'Newcomer',
                'Pre Bronze'=>'Pre Bronze',
                'Intermediate Bronze'=>'Intermediate Bronze',
                'Full Bronze'=>'Full Bronze',
                'Open Bronze' =>'Open Bronze',
                'Pre Silver'=>'Pre Silver',
                'Intermediate Silver'=>'Intermediate Silver',
                'Full Silver'=>'Full Silver',
                'Open Silver' =>'Open Silver',
                'Pre Gold'=>'Pre Gold',
                'Intermediate Gold'=>'Intermediate Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2',
            ],

        'Pre Bronze'=>
            [
                'Newcomer'=>'Pre Bronze',
                'Pre Bronze'=>'Pre Bronze',
                'Intermediate Bronze'=>'Intermediate Bronze',
                'Full Bronze'=>'Full Bronze',
                'Open Bronze' =>'Open Bronze',
                'Pre Silver'=>'Pre Silver',
                'Intermediate Silver'=>'Intermediate Silver',
                'Full Silver'=>'Full Silver',
                'Open Silver' =>'Open Silver',
                'Pre Gold'=>'Pre Gold',
                'Intermediate Gold'=>'Intermediate Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],

        'Intermediate Bronze'=>
            [
                'Newcomer'=>'Intermediate Bronze',
                'Pre Bronze'=>'Intermediate Bronze',
                'Intermediate Bronze'=>'Intermediate Bronze',
                'Full Bronze'=>'Full Bronze',
                'Open Bronze' =>'Open Bronze',
                'Pre Silver'=>'Pre Silver',
                'Intermediate Silver'=>'Intermediate Silver',
                'Full Silver'=>'Full Silver',
                'Open Silver' =>'Open Silver',
                'Pre Gold'=>'Pre Gold',
                'Intermediate Gold'=>'Intermediate Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],

        'Full Bronze'=>
            [
                'Newcomer'=>'Full Bronze',
                'Pre Bronze'=>'Full Bronze',
                'Intermediate Bronze'=>'Full Bronze',
                'Full Bronze'=>'Full Bronze',
                'Open Bronze' =>'Open Bronze',
                'Pre Silver'=>'Pre Silver',
                'Intermediate Silver'=>'Intermediate Silver',
                'Full Silver'=>'Full Silver',
                'Open Silver' =>'Open Silver',
                'Pre Gold'=>'Pre Gold',
                'Intermediate Gold'=>'Intermediate Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],

        'Open Bronze' =>
            [
                'Newcomer'=>'Open Bronze',
                'Pre Bronze'=>'Open Bronze',
                'Intermediate Bronze'=>'Open Bronze',
                'Full Bronze'=>'Open Bronze',
                'Open Bronze' =>'Open Bronze',
                'Pre Silver'=>'Pre Silver',
                'Intermediate Silver'=>'Intermediate Silver',
                'Full Silver'=>'Full Silver',
                'Open Silver' =>'Open Silver',
                'Pre Gold'=>'Pre Gold',
                'Intermediate Gold'=>'Intermediate Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],

        'Pre Silver'=>
            [
                'Newcomer'=>'Pre Silver',
                'Pre Bronze'=>'Pre Silver',
                'Intermediate Bronze'=>'Pre Silver',
                'Full Bronze'=>'Pre Silver',
                'Open Bronze' =>'Pre Silver',
                'Pre Silver'=>'Pre Silver',
                'Intermediate Silver'=>'Intermediate Silver',
                'Full Silver'=>'Full Silver',
                'Open Silver' =>'Open Silver',
                'Pre Gold'=>'Pre Gold',
                'Intermediate Gold'=>'Intermediate Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],


        'Intermediate Silver'=>
            [
                'Newcomer'=>'Intermediate Silver',
                'Pre Bronze'=>'Intermediate Silver',
                'Intermediate Bronze'=>'Intermediate Silver',
                'Full Bronze'=>'Intermediate Silver',
                'Open Bronze' =>'Intermediate Silver',
                'Pre Silver'=>'Intermediate Silver',
                'Intermediate Silver'=>'Intermediate Silver',
                'Full Silver'=>'Full Silver',
                'Open Silver' =>'Open Silver',
                'Pre Gold'=>'Pre Gold',
                'Intermediate Gold'=>'Intermediate Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],

        'Full Silver'=>
            [
                'Newcomer'=>'Full Silver',
                'Pre Bronze'=>'Full Silver',
                'Intermediate Bronze'=>'Full Silver',
                'Full Bronze'=>'Full Silver',
                'Open Bronze' =>'Full Silver',
                'Pre Silver'=>'Full Silver',
                'Intermediate Silver'=>'Full Silver',
                'Full Silver'=>'Full Silver',
                'Open Silver' =>'Open Silver',
                'Pre Gold'=>'Pre Gold',
                'Intermediate Gold'=>'Intermediate Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],

        'Open Silver' =>
            [
                'Newcomer'=>'Open Silver',
                'Pre Bronze'=>'Open Silver',
                'Intermediate Bronze'=>'Open Silver',
                'Full Bronze'=>'Open Silver',
                'Open Bronze' =>'Open Silver',
                'Pre Silver'=>'Open Silver',
                'Intermediate Silver'=>'Open Silver',
                'Full Silver'=>'Open Silver',
                'Open Silver' =>'Open Silver',
                'Pre Gold'=>'Pre Gold',
                'Intermediate Gold'=>'Intermediate Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],

        'Pre Gold'=>
            [
                'Newcomer'=>'Pre Gold',
                'Pre Bronze'=>'Pre Gold',
                'Intermediate Bronze'=>'Pre Gold',
                'Full Bronze'=>'Pre Gold',
                'Open Bronze' =>'Pre Gold',
                'Pre Silver'=>'Pre Gold',
                'Intermediate Silver'=>'Pre Gold',
                'Full Silver'=>'Pre Gold',
                'Open Silver' =>'Pre Gold',
                'Pre Gold'=>'Pre Gold',
                'Intermediate Gold'=>'Intermediate Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],

        'Intermediate Gold'=>
            [
                'Newcomer'=>'Intermediate Gold',
                'Pre Bronze'=>'Intermediate Gold',
                'Intermediate Bronze'=>'Intermediate Gold',
                'Full Bronze'=>'Intermediate Gold',
                'Open Bronze' =>'Intermediate Gold',
                'Pre Silver'=>'Intermediate Gold',
                'Intermediate Silver'=>'Intermediate Gold',
                'Full Silver'=>'Intermediate Gold',
                'Open Silver' =>'Intermediate Gold',
                'Pre Gold'=>'Intermediate Gold',
                'Intermediate Gold'=>'Intermediate Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],

        'Full Gold'=>
            [
                'Newcomer'=>'Full Gold',
                'Pre Bronze'=>'Full Gold',
                'Intermediate Bronze'=>'Full Gold',
                'Full Bronze'=>'Full Gold',
                'Open Bronze' =>'Full Gold',
                'Pre Silver'=>'Full Gold',
                'Intermediate Silver'=>'Full Gold',
                'Full Silver'=>'Full Gold',
                'Open Silver' =>'Full Gold',
                'Pre Gold'=>'Full Gold',
                'Intermediate Gold'=>'Full Gold',
                'Full Gold'=>'Full Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],


        'Open Gold'=>
            [
                'Newcomer'=>'Open Gold',
                'Pre Bronze'=>'Open Gold',
                'Intermediate Bronze'=>'Open Gold',
                'Full Bronze'=>'Open Gold',
                'Open Bronze' =>'Open Gold',
                'Pre Silver'=>'Open Gold',
                'Intermediate Silver'=>'Open Gold',
                'Full Silver'=>'Open Gold',
                'Open Silver' =>'Open Gold',
                'Pre Gold'=>'Open Gold',
                'Intermediate Gold'=>'Open Gold',
                'Full Gold'=>'Open Gold',
                'Open Gold'=>'Open Gold',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],

        'Gold Star 1'=>
            [
                'Newcomer'=>'Gold Star 1',
                'Pre Bronze'=>'Gold Star 1',
                'Intermediate Bronze'=>'Gold Star 1',
                'Full Bronze'=>'Gold Star 1',
                'Open Bronze' =>'Gold Star 1',
                'Pre Silver'=>'Gold Star 1',
                'Intermediate Silver'=>'Gold Star 1',
                'Full Silver'=>'Gold Star 1',
                'Open Silver' =>'Gold Star 1',
                'Pre Gold'=>'Gold Star 1',
                'Intermediate Gold'=>'Gold Star 1',
                'Full Gold'=>'Gold Star 1',
                'Open Gold'=>'Gold Star 1',
                'Gold Star 1'=>'Gold Star 1',
                'Gold Star 2'=>'Gold Star 2'
            ],

        'Gold Star 2'=>
            [
                'Newcomer'=>'Gold Star 2',
                'Pre Bronze'=>'Gold Star 2',
                'Intermediate Bronze'=>'Gold Star 2',
                'Full Bronze'=>'Gold Star 2',
                'Open Bronze' =>'Gold Star 2',
                'Pre Silver'=>'Gold Star 2',
                'Intermediate Silver'=>'Gold Star 2',
                'Full Silver'=>'Gold Star 2',
                'Open Silver' =>'Gold Star 2',
                'Pre Gold'=>'Gold Star 2',
                'Intermediate Gold'=>'Gold Star 2',
                'Full Gold'=>'Gold Star 2',
                'Open Gold'=>'Gold Star 2',
                'Gold Star 1'=>'Gold Star 2',
                'Gold Star 2'=>'Gold Star 2'
            ]
    ];


    const HIGH_MODEL=
        [
             'ISTD Medal Exams'=>
                    [
                        'ISTD Medal Exams'=>'ISTD Medal Exams',
                        'Georgia DanceSport Amateur'=>'Georgia DanceSport Amateur',
                        'Georgia DanceSport ProAm'=>'Georgia DanceSport ProAm'
                    ],
             'Georgia DanceSport Amateur'=>
                    [
                        'ISTD Medal Exams'=>'Georgia DanceSport Amateur',
                        'Georgia DanceSport Amateur'=>'Georgia DanceSport Amateur',
                        'Georgia DanceSport ProAm'=>'Georgia DanceSport ProAm'
                    ],

             'Georgia DanceSport ProAm'=>
                    [
                        'ISTD Medal Exams'=>'Georgia DanceSport ProAm',
                        'Georgia DanceSport Amateur'=>'Georgia DanceSport ProAm',
                        'Georgia DanceSport ProAm'=>'Georgia DanceSport ProAm'
                    ]
        ];

    const PROFICIENCY_AMATEUR_TO_MEDAL =
        [
            'Newcomer'=>'Pre Bronze',
            'Bronze'=>'Bronze',
            'Silver'=>'Silver',
            'Gold'=>'Gold',
            'Novice'=>'Gold',
            'Pre Championship'=>'Gold',
            'Championship'=>'Gold'
        ];

    const PROFICIENCY_STUDENT_TO_AMATEUR =
        [
             'Newcomer'=>'Newcomer',
             'Pre Bronze'=>'Bronze',
             'Intermediate Bronze'=>'Bronze',
             'Full Bronze'=>'Bronze',
             'Open Bronze'=>'Bronze',
             'Pre Silver'=>'Silver',
             'Intermediate Silver'=>'Silver',
             'Full Silver'=>'Silver',
             'Open Silver' =>'Silver',
             'Pre Gold'=>'Gold',
             'Intermediate Gold'=>'Gold',
             'Full Gold'=>'Gold',
             'Open Gold' =>'Novice',
             'Gold Star 1' =>'Pre Championship',
             'Gold Star 2' =>'Championship'
        ];

    const PROFICIENCY_STUDENT_TO_MEDAL = 
        [
            'Newcomer'=>'Pre Bronze',
            'Pre Bronze'=>'Bronze',
            'Intermediate Bronze'=>'Bronze',
            'Full Bronze'=>'Bronze',
            'Open Bronze'=>'Bronze',
            'Pre Silver'=>'Silver',
            'Intermediate Silver'=>'Silver',
            'Full Silver'=>'Silver',
            'Open Silver' =>'Silver',
            'Pre Gold'=>'Gold',
            'Intermediate Gold'=>'Gold',
            'Full Gold'=>'Gold',
            'Open Gold' =>'Gold'
        ];

    const PROFICIENCY = 1;
    const AGE = 2;
    const TYPE = 3;
    const TEXT = [self::PROFICIENCY=>'proficiency',
                  self::AGE=>'age',
                  self::TYPE=>'type'];
    const NO_EVALUATION = "Invalid participant combination for %s. Georgia DanceSport cannot evaluate %s.";
    const MESSAGE_COUPLING = 'coupling not available';
    const MESSAGE_SUPPORT = 'contact support';

    /**
     * @param Model $model
     * @param int $evaluate
     * @return \Closure
     */
    protected function AmateurAmateur(Model $model, int $evaluate):\Closure
    {
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Value $genre, Participant $p1, Participant $p2=null) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                           return $this->medalCoupleProficiency($genre,$p1,$p2);
                        case 'Georgia DanceSport Amateur':
                            return $this->amateurCoupleProficiency($genre, $p1,$p2);
                    
                        default:
                            throw new ClassifyException('Unsupported model',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
                break;
            case self::AGE:
                return function(Participant $p1, Participant $p2=null) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                            return $this->medalCoupleAge($p1,$p2);
                        case 'Georgia DanceSport Amateur':
                            return $p2? $this->amateurCoupleAge($p1,$p2):
                                        $this->amateurAge($p1);
                        default:
                            throw new ClassifyException('Unsupported model',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
            case self::TYPE:
                /**
                 * @param Participant $p1
                 * @param Participant $p2
                 * @return Value|null
                 */
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                            return $this->domainValueHash['type']['Couple'];
                        case 'Georgia DanceSport Amateur':
                            return $this->domainValueHash['type']['Amateur'];
                        case 'Georgia DanceSport ProAm':
                            return $this->domainValueHash['type']['Teacher-Student'];
                        default:
                            $message=sprintf("No type classification for %s and %s",
                                $p1->getName(),$p2->getName());
                            throw new ClassifyException('Classification failure',
                                $message,
                                9000);
                    }
                };
        }
        return null;
    }

    protected function TeacherStudent(Model $model,int $evaluate):\Closure
    {
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Value $genre, Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                            return $this->medalTeacherStudentProficiency($genre,$p1,$p2);
                        case 'Georgia DanceSport ProAm':
                            return $this->proamTeacherStudentProficiency($genre,$p1,$p2);
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
                break;
            case self::AGE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                            return $this->medalTeacherStudentAge($p1,$p2);
                        case 'Georgia DanceSport ProAm':
                            return $this->proamTeacherStudentAge($p1,$p2);
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
            case self::TYPE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                            return  $this->domainValueHash['type']['Solo'];
                        case 'Georgia DanceSport ProAm':
                            return $this->domainValueHash['type']['Teacher-Student'];
                        default:
                            $message = sprintf('Unable to determine type for %s and %s',$p1->getName(),$p2->getName());
                            throw new ClassifyException('No type',
                                $message,
                                9000);
                    }
                };
        }
        return null;
    }

    private function SoloParticipant(Model $model, int $evaluate): \Closure 
    {
        switch($evaluate)
        {
            case self::PROFICIENCY:
                return function(Value $genre, Participant $p) use ($model){
                    switch($model->getName())
                    {
                        case 'Georgia DanceSport Amateur':
                            return $p->fetchGenreProficiency($genre);
                    }
                    return null;
                };

            case self::AGE:
                return function(Participant $p) use ($model) {
                    $years = $p->getYears();
                    switch ($model->getName()) {
                        case 'Georgia DanceSport Amateur':
                            foreach (self::AGE_STUDENT as $break => $age) {
                                if ($years >= $break) {
                                    return $this->domainValueHash['age'][$age];
                                }
                            }
                    };
                    return null;
                };
                break;
            case self::TYPE:
                return function (Participant $p) use ($model) {
                    return $p->getTypeA();
                };
        }
        return null;
    }
    

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Participant
     */
    private function elder(Participant $p1, Participant $p2):?Participant
    {
        return $p1->getYears()>=$p2->getYears()?$p1:$p2;
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Participant
     */
    private function younger(Participant $p1, Participant $p2):?Participant
    {
        return $p1->getYears()<$p2->getYears()?$p1:$p2;
    }


    private function student(Participant $p1, Participant $p2):?Participant
    {
        return $p1->getTypeB()->getName()=='Student'?$p1:$p2;
    }
    
    

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return ArrayCollection
     */
    private function commonGenres(Participant $p1, Participant $p2):ArrayCollection
    {
        $genreNames = array_intersect($p1->fetchGenreNames(),$p2->fetchGenreNames());
        $values = new ArrayCollection();
        foreach($genreNames as $name) {
            $value = isset($this->domainValueHash['style'][$name])?
                $this->domainValueHash['style'][$name]:
                $this->domainValueHash['substyle'][$name];
            $values->set($name,$value);
        }
        return $values;
    }

    private function commonModels(Participant $p1, Participant $p2): array
    {
        $r=[];
        $modelNames=array_intersect($p1->getModelIds(false),$p2->getModelIds(false));
        foreach($modelNames as $name) {
            $r[$name]=$p1->getModels()->get($name);
        }
        return $r;
    }


    
    
    /**
     * @param $models
     * @return Model
     */
    private function highModel($models):?Model
    {
        $collection=array_values($models);
        switch(count($collection)) {
            case 1:
                /** @var Model $m1 */
                list($m1)=$collection;
                return $m1;
            case 2:
                /**
                 * @var Model $m1
                 * @var Model $m2
                 */
                list($m1,$m2)=$collection;
                $higherName=self::HIGH_MODEL[$m1->getName()][$m2->getName()];
                $higherModel=$higherName==$m1->getName()?$m1:$m2;
                return $higherModel;
            case 3:
                /**
                 * @var Model $m1
                 * @var Model $m2
                 * @var Model $m3
                 */

                list($m1,$m2,$m3)=$collection;
                $higherName=self::HIGH_MODEL[$m1->getName()][$m2->getName()];
                $higherModel=$higherName==$m1->getName()?$m1:$m2;
                $highestName = self::HIGH_MODEL[$higherModel->getName()][$m3->getName()];
                $highestModel= $highestName==$higherModel->getName()?$higherModel:$m3;
                return $highestModel;
        }
        return null;
    }
    
    private function participantModel(Participant $participant):Model
    {
        $models = $participant->getModels()->toArray();
        $highModel = $this->highModel($models);
        return $highModel;
    }
    
    
    private function medalProficiency(Value $genre, Participant $participant):?Value
    {
        $proficiencyValue = $participant->fetchGenreProficiency($genre);
        $proficiencyName = $proficiencyValue->getName();
        $model  = $this->participantModel($participant);
        switch($model->getName()){
            case 'ISTD Medal Exams':
                return $this->domainValueHash['proficiency'][$proficiencyName];
            case 'Georgia DanceSport Amateur':
                $medalName = self::PROFICIENCY_AMATEUR_TO_MEDAL[$proficiencyName];    
                return $this->domainValueHash['proficiency'][$medalName];
            case 'Georgia DanceSport ProAm':
                $medalName = self::PROFICIENCY_STUDENT_TO_MEDAL[$proficiencyName];
                return $this->domainValueHash['proficiency'][$medalName];
        }
        return null;
    }
    
    private function amateurProficiency(Value $genre, Participant $participant):?Value
    {
        $proficiencyValue = $participant->fetchGenreProficiency($genre);
        $proficiencyName = $proficiencyValue->getName();
        $model = $this->participantModel($participant);
        switch($model->getName()){
            case 'Georgia DanceSport Amateur':
                return $this->domainValueHash['proficiency'][$proficiencyName];
            case 'Georgia DanceSport ProAm':
                $amateurName = self::PROFICIENCY_STUDENT_TO_AMATEUR[$proficiencyName];
                return $this->domainValueHash['proficiency'][$amateurName];
        }
        return null;
    }
    

    private function studentProficiency(Value $genre, Participant $participant):?Value
    {
        $proficiencyValue = $participant->fetchGenreProficiency($genre);
        $proficiencyName = $proficiencyValue->getName();
        $model = $this->participantModel($participant);
        switch($model->getName()){
            case 'Georgia DanceSport ProAm':
                return $this->domainValueHash['proficiency'][$proficiencyName];
        }
        return null;
    }

    
    private function medalCoupleProficiency(Value $genre, Participant $p1, Participant $p2):Value
    {
        $p1Proficiency=$this->medalProficiency($genre, $p1);
        $p2Proficiency=$this->medalProficiency($genre, $p2);
        $higherProficiencyName=self::HIGHER_PROFICIENCY_MEDAL[$p1Proficiency->getName()][$p2Proficiency->getName()];
        return $this->domainValueHash['proficiency'][$higherProficiencyName];
    }    
    
    private function medalTeacherStudentProficiency(Value $genre, Participant $p1, Participant $p2): Value
    {
        $student = $this->student($p1,$p2);
        $proficiencyValue = $this->medalProficiency($genre,$student);
        return $proficiencyValue;
    }

    private function proamTeacherStudentProficiency(Value $genre, Participant $p1, Participant $p2): Value
    {
        $student = $this->student($p1,$p2);
        $proficiencyValue = $this->studentProficiency($genre,$student);
        return $proficiencyValue;
    }
    
    private function amateurCoupleProficiency(Value $genre, Participant $p1, Participant $p2):Value
    {
        $p1Proficiency=$this->amateurProficiency($genre, $p1);
        $p2Proficiency=$this->amateurProficiency($genre, $p2);
        $higherProficiencyName=self::HIGHER_PROFICIENCY_AMATEUR[$p1Proficiency->getName()][$p2Proficiency->getName()];
        return $this->domainValueHash['proficiency'][$higherProficiencyName];
    }
    
    
    
    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Value
     * @throws ClassifyException
     */
    private function medalCoupleAge(Participant $p1,Participant $p2): ?Value
    {
        $elder=$this->elder($p1,$p2);
        $elderYears = $elder->getYears();
        $younger=$this->younger($p1,$p2);
        $youngerYears = $younger->getYears();
        foreach(self::AGE_MEDAL as $ageBreak=>$name) {
            if($elderYears<16 && $elderYears>=$ageBreak) {
                return $this->domainValueHash['age'][self::AGE_MEDAL[$ageBreak]];
            }
            if ($youngerYears<16 && $elderYears>=16 && $elderYears>=$ageBreak) {
               return $this->domainValueHash['age'][self::AGE_MEDAL[$ageBreak]];
            }
            if ($youngerYears>=$ageBreak) {
                return $this->domainValueHash['age'][self::AGE_MEDAL[$ageBreak]];
            }
        }
        $name = $p1->getName().' & '.$p2->getName();
        throw new ClassifyException('iSTD Age',"Unable to classify age for $name",9000);
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Value|null
     * @throws ClassifyException
     */
    private function medalTeacherStudentAge(Participant $p1,Participant $p2): ?Value
    {
        $student = $this->student($p1,$p2);
        $years = $student->getYears();
        foreach(self::AGE_MEDAL as $ageBreak=>$ageName){
            if($years>=$ageBreak) {
                return $this->domainValueHash['age'][$ageName];
            }
        }
        $name = $student->getName();
        throw new ClassifyException("ISTD Age", "Unable to classify age for $name", 9000);
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Value
     * @throws ClassifyException
     */
    private function proamTeacherStudentAge(Participant $p1, Participant $p2): Value
    {
        $student = $this->student($p1,$p2);
        $years = $student->getYears();
        foreach(self::AGE_STUDENT as $ageBreak=>$ageName){
            if($years>=$ageBreak){
                return $this->domainValueHash['age'][$ageName];
            }
        }
        $name = $student->getName();
        throw new ClassifyException("ProAm Age", "Unable to classify age for $name", 9000);
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Value|null
     * @throws ClassifyException
     */
    private function amateurCoupleAge(Participant $p1,Participant $p2): ?Value
    {
        $elder=$this->elder($p1,$p2);
        $elderYears = $elder->getYears();
        $younger=$this->younger($p1,$p2);
        $youngerYears = $younger->getYears();
        foreach(self::AGE_AMATEUR as $ageElder=>$youngerAges) {
            if($elderYears >= $ageElder) {
                foreach($youngerAges as $ageYounger=>$ageName) {
                    if($youngerYears>=$ageYounger){
                        $value = $this->domainValueHash['age'][$ageName];
                        return $value;
                    };
                }
            }
        }
        $name = $p1->getName().' & '.$p2->getName();
        throw new ClassifyException('Georgia DanceSport Age',"Unable to classify age for $name",9000);
    }

    public function amateurAge(Participant $participant):?Value
    {
        $years = $participant->getYears();
        foreach(self::AGE_STUDENT as $nominalYears=>$ageName){
            if($years>=$nominalYears){
                return $this->domainValueHash['age'][$ageName];
            }
        }
        return null;
    }


    /**
     * @param Participant $p1
     * @param Participant $p2
     * @param Model $model
     * @return null|string
     */
    private function coupleClassifier(Participant $p1, Participant $p2, Model $model): ?string
    {
        $p1TypeA = $p1->getTypeA()->getName();
        $p1TypeB = $p1->getTypeB()->getName();
        $p2TypeA = $p2->getTypeA()->getName();
        $p2TypeB = $p2->getTypeB()->getName();
        $classifier=$p1TypeA.$p1TypeB.$p2TypeA.$p2TypeB;
        switch($classifier) {
            case 'AmateurStudentAmateurStudent':
            case 'AmateurTeacherAmateurTeacher':
                switch($model->getName()){
                    case 'ISTD Medal Exams':
                    case 'Georgia DanceSport Amateur':
                        return 'AmateurAmateur';
                    case 'Georgia DanceSport ProAm':
                        return null;
                }
                break;
            case 'AmateurStudentAmateurTeacher':
            case 'AmateurTeacherAmateurStudent':
            case 'ProfessionalTeacherAmateurStudent':
            case 'AmateurStudentProfessionalTeacher':
                switch($model->getName()){
                    case 'ISTD Medal Exams':
                        return 'TeacherStudent';
                    case 'Georgia DanceSport Amateur':
                        return null;
                    case 'Georgia DanceSport ProAm':
                        return 'TeacherStudent';
                }
                break;
            default:
                return null;
        }
        return null;
    }

    public function buildCoupleQualification(Participant $p1, Participant $p2,
                                                Value $genre,Value $proficiency,Value $age,Value $type):?Qualification
    {
        $qualification = new Qualification();
        if($genre->getName()!=='Fun Events') {
            $qualification->set([$genre,$proficiency,$age,$type]);
            return $qualification;
        }
        $yearsDifference = abs($p1->getYears()-$p2->getYears());
        $seniorFunAge=$yearsDifference>=40?$this->domainValueHash['age']['Senior Youngster']:null;
        $adultFunAge=$yearsDifference<40 && $yearsDifference>=20?$this->domainValueHash['age']['Adult Youngster']:null;

        if($seniorFunAge){
            $qualification->set([$genre,$proficiency,$seniorFunAge,$type]);
            return $qualification;
        }

        if($adultFunAge){
            $qualification->set([$genre,$proficiency,$adultFunAge,$type]);
            return $qualification;
        }
        return null;
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Player
     */
    public function couple(Participant $p1, Participant $p2) : Player
    {
        /** @var ArrayCollection $commonGenreValues */
        $player = new Player();
        $player->addParticipant( $p1 )
                ->addParticipant( $p2 );
        $commonModels = $this->commonModels( $p1, $p2 );
        foreach ($commonModels as $modelName => $model) {
            $classifier = $this->coupleClassifier($p1,$p2,$model);
            if(is_null($classifier)){
                //Ineligible to enter this model continue to the next
                continue;
            }
            $proficiencyFn = $this->$classifier( $model, self::PROFICIENCY );
            $ageFn = $this->$classifier( $model, self::AGE );
            $typeFn = $this->$classifier( $model, self::TYPE );
            /** @var Value $age */
            $age = $ageFn( $p1, $p2 );
            /** @var Value $type */
            $type = $typeFn( $p1, $p2 );
            /** @var ArrayCollection $genreCollection */
            $genreCollection = $this->commonGenres( $p1, $p2 );
            $iterator = $genreCollection->getIterator();

            /** @var Value $genre */
            while ($genre = $iterator->current()) {
                /** @var bool $isFunEvent */
                $proficiency = $proficiencyFn( $genre, $p1, $p2 );
                /** @var Qualification $qualification */
                $qualification=$this->buildCoupleQualification($p1,$p2,$genre,$proficiency,$age,$type);
                if($qualification){
                    $player->addQualification( $model, $qualification );
                }
                $iterator->next();
            }
        }
        return $player;
    }


    public function buildSoloQualification(Participant $p,
                                           Value $genre,Value $proficiency,Value $age,Value $type):?Qualification
    {
        $qualification = new Qualification();
        if($genre->getName()!=='Fun Events'){
            $qualification->set([$genre,$proficiency,$age,$type]);
            return $qualification;
        }
        $funAge=in_array($age->getName(),['Baby','Juvenile','Preteen 1','Preteen 2'])?
                         $age:null;
        if($funAge){
            $qualification->set([$genre,$proficiency,$funAge,$type]);
            return $qualification;
        }
        return null;
    }

        /**
     * @param Participant $p
     * @return Player
     */
    public function solo(Participant $p) : Player
    {
        $models = $p->getModels()->toArray();
        /** @var Player $player */
        $player = new Player();
        $player->addParticipant($p);
        foreach($models as $modelName=>$model)
        {
            $proficiencyFn = $this->SoloParticipant( $model, self::PROFICIENCY );
            $ageFn = $this->SoloParticipant( $model, self::AGE );
            $typeFn = $this->SoloParticipant( $model, self::TYPE );
            foreach($p->fetchGenreNames() as $genre) {
                $genre=isset($this->domainValueHash['style'][$genre])?
                            $this->domainValueHash['style'][$genre]:
                            $this->domainValueHash['substyle'][$genre];
                $proficiency=$proficiencyFn($genre,$p);
                $age = $ageFn($p);
                $type= $typeFn($p);
                $qualification=$this->buildSoloQualification($p,$genre,$proficiency,$age,$type);
                if(!$qualification){
                    continue;
                }
                $player->addQualification($model,$qualification);
            }

        }
        return $player;
    }
}