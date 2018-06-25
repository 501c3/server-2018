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
use App\Exceptions\PlayerException;
use Doctrine\Common\Collections\ArrayCollection;


class GeorgiaDanceSportClassify extends Classify
{

    const AGE_AMATEUR  = [75=>[70=>'Senior 5',
                               60=>'Senior 4',
                               50=>'Senior 3',
                               40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult',
                                2=>'Senior Youngster'],
                          65=>[60=>'Senior 4',
                               50=>'Senior 3',
                               40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult',
                                2=>'Senior Youngster'],
                          55=>[50=>'Senior 3',
                               40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult',
                               18=>'Senior Youngster'],
                          45=>[40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult',
                               18=>'Adult Youngster',
                                2=>'Senior Youngster'],
                          35=> [30=>'Senior 1',
                                19=>'Adult',
                                 2=>'Adult Youngster'],
                          19=> [14=>'Adult'],
                          16=> [2=>'Youth'],
                          14=> [2=>'Junior 2'],
                          12=> [2=>'Junior 1'],
                          10=> [2=>'Preteen 2'],
                           7=> [2=>'Preteen 1'],
                           5=> [2=>'Juvenile'],
                           2=> [2=>'Baby']];

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


    const AGE_EXAMS =[50=>'Senior 50',
                      16=>'Adult 16-50',
                      12=>'Junior 12-16',
                      8=>'Under 12',
                      6=>'Under 8',
                      1=>'Under 6'];

    const HIGHER_PROFICIENCY_AMATEUR = [
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

    const PROFICIENCY_AMATEUR_ISTD =
        [
            'Newcomer'=>'Pre Bronze',
            'Bronze'=>'Bronze',
            'Silver'=>'Silver',
            'Gold'=>'Gold',
            'Novice'=>'Gold',
            'Pre Championship'=>'Gold',
            'Championship'=>'Gold'
        ];

    const PROFICIENCY_STUDENT_AMATEUR =
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
    protected function AmateurStudentAmateurStudent(Model $model, int $evaluate){

        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Value $genre, Participant $p1, Participant $p2=null) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                           return $p2?$this->istdAmateurStudentAmateurStudentProficiency($genre,$p1,$p2):
                                    $this->istdAmateurStudentProficiency($genre, $p1);
                        case 'Georgia DanceSport Amateur':
                            return $p2?$this->gadsamAmateurStudentAmateurStudentProficiency($genre, $p1,$p2):
                                        $this->gadsamAmateurStudentProficiency($genre, $p1);
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
                break;
            case self::AGE:
                return function(Participant $p1, Participant $p2=null) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                            return $p2?$this->istdAmateurStudentAmateurStudentAge($p1,$p2):
                                        $this->istdAmateurStudentAge($p1);
                        case 'Georgia DanceSport Amateur':
                            return $p2? $this->gadsamAmateurStudentAmateurStudentAge($p1,$p2):
                                        $this->gadsamAmateurStudentAge($p1);
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
                              return $this->domainValueHash['type']['Couple'];
                        case 'Georgia DanceSport Amateur':
                              return $this->domainValueHash['type']['Amateur'];

                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
        }
    }


    protected function AmateurTeacherAmateurTeacher(Model $model, int $evaluate){
        $message = sprintf(self::NO_EVALUATION,$model->getName(),self::TEXT[$evaluate]);
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Value $genre, Participant $p1, Participant $p2=null) use ($model,$message): ?Value{
                    switch ($model->getName()){
                        case 'Georgia DanceSport Amateur':
                            return $p2?$this->gadsamAmateurStudentAmateurStudentProficiency($genre, $p1,$p2):
                                $this->gadsamAmateurStudentProficiency($genre, $p1);
                        default:
                            throw new PlayerException($message,9000,$p1,$p2);
                    }
                };
                break;
            case self::AGE:
                return function(Participant $p1, Participant $p2=null) use ($model,$message): ?Value{
                    switch ($model->getName()){
                        case 'Georgia DanceSport Amateur':
                            return $p2? $this->gadsamAmateurStudentAmateurStudentAge($p1,$p2):
                                $this->gadsamAmateurStudent($p1);
                        default:
                            throw new PlayerException($message,9000,$p1,$p2);
                    }
                };
            case self::TYPE:
                return function(Participant $p1, Participant $p2) use ($model,$message): ?Value{
                    switch ($model->getName()){
                        case 'Georgia DanceSport Amateur':
                            return $this->domainValueHash['type']['Amateur'];
                        default:
                            throw new PlayerException($message,9000,$p1,$p2);
                    }
                };
        }
    }

    protected function ProfessionalStudentProfessionalStudent(Model $model,int $evaluate){
        $message = sprintf(self::NO_EVALUATION,$model->getName(),self::TEXT[$evaluate]);
        return function (Participant $p1,Participant $p2) use($message) {
            throw new PlayerException($message,9000,$p1,$p2);
        };
    }

    protected function ProfessionalTeacherProfessionalTeacher(Model $model,int $evaluate){
        $message = sprintf(self::NO_EVALUATION,$model->getName(),self::TEXT[$evaluate]);
        return function (Participant $p1,Participant $p2) use($message) {
            throw new PlayerException($message,9000,$p1,$p2);
        };
    }


    private function AmateurStudentAmateurTeacher(Model $model,int $evaluate){
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                            //TODO:
                        case 'Georgia DanceSport Amateur':
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);

                        case 'Georgia DanceSport ProAm':
                            //TODO:
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

                        case 'Georgia DanceSport Amateur':
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);

                        case 'Georgia DanceSport ProAm':
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

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
        }
    }

    private function AmateurStudentProfessionalStudent(Model $model,int $evaluate){
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                            //TODO:
                        case 'Georgia DanceSport Amateur':
                            //TODO:
                        case 'Georgia DanceSport ProAm':
                            //TODO:
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
                             //TODO:
                        case 'Georgia DanceSport Amateur':
                             //TODO:
                        case 'Georgia DanceSport ProAm':
                            //TODO:
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

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
        }
    }

    private function AmateurStudentProfessionalTeacher(Model $model,int $evaluate){
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Value $genre, Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'Georgia DanceSport ProAm':
                            return $this->gadsproamTeacherStudentProficiency($genre,$p1,$p2);
                    }
                };
                break;
            case self::AGE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'Georgia DanceSport ProAm':
                            return $this->gadsproamTeacherStudentAge($p1,$p2);
                    }
                };
                break;
            case self::TYPE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'Georgia DanceSport ProAm':
                            return $this->domainValueHash['type']['Teacher-Student'];
                    }
                };
        }
    }


    private function AmateurTeacherAmateurStudent(Model $model,int $evaluate){
        return $this->AmateurStudentProfessionalTeacher($model,$evaluate);
    }



    private function AmateurTeacherProfessionalStudent(Model $model,int $evaluate){
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
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
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
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

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
        }
    }

    private function AmateurTeacherProfessionalTeacher(Model $model,int $evaluate){
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Participant $p1, Participant $p2) use ($model): ?Value
                {
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                        case 'Georgia DanceSport Amateur':
                                //TODO:
                        case 'Georgia DanceSport ProAm':
                                //TODO:
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
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);

                        case 'Georgia DanceSport Amateur':
                                //TODO:
                        case 'Georgia DanceSport ProAm':
                                //TODO:
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

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
        }
    }


    private function ProfessionalStudentAmateurStudent(Model $model,int $evaluate){
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':
                            //TODO:
                        case 'Georgia DanceSport Amateur':
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                        case 'Georgia DanceSport ProAm':
                            //TODO:
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
                            //TODO:
                        case 'Georgia DanceSport Amateur':
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                        case 'Georgia DanceSport ProAm':
                            //TODO:
                    }
                };
            case self::TYPE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
        }
    }

    private function ProfessionalStudentAmateurTeacher(Model $model,int $evaluate){
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                        default:
                            throw new ClassifyException('Professional-Teacher',
                                self::MESSAGE_COUPLING,
                                9000);
                    }
                };
                break;
            case self::AGE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                        default:
                            throw new ClassifyException('Professional-Teacher',
                                self::MESSAGE_COUPLING,
                                9000);
                    }
                };
            case self::TYPE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };

        }
    }


    private function ProfessionalStudentProfessionalTeacher(Model $model,int $evaluate){
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        default:
                            throw new ClassifyException('Professional-Professional',
                                self::MESSAGE_COUPLING,
                                9000);
                    }
                };
                break;
            case self::AGE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        default:
                            throw new ClassifyException('Professional-Professional',
                                self::MESSAGE_COUPLING,
                                9000);

                    }
                };
                break;
            case self::TYPE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
        }
    }

    private function ProfessionalTeacherAmateurStudent(Model $model,int $evaluate){
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        //TODO: All are available
                        case 'ISTD Medal Exams':

                        case 'Georgia DanceSport ProAm':
                    }
                };
                break;
            case self::AGE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        //TODO: All are available
                        case 'ISTD Medal Exams':

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                    }
                };
                break;
            case self::TYPE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };

        }
    }

    private function ProfessionalTeacherAmateurTeacher(Model $model,int $evaluate){
        switch ($evaluate){
            case self::PROFICIENCY:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        default:
                            throw new ClassifyException('Professional-Amateur Teacher',
                                self::MESSAGE_COUPLING,
                                9000);
                    }
                };
                break;
            case self::AGE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        default:
                            throw new ClassifyException('Professional-Amateur Teacher',
                                self::MESSAGE_COUPLING,
                                9000);
                    }
                };
                break;
            case self::TYPE:
                return function(Participant $p1, Participant $p2) use ($model): ?Value{
                    switch ($model->getName()){
                        case 'ISTD Medal Exams':

                        case 'Georgia DanceSport Amateur':

                        case 'Georgia DanceSport ProAm':
                        default:
                            throw new ClassifyException('Configuration error',
                                self::MESSAGE_SUPPORT,
                                9000);
                    }
                };
        }
    }

    private function ProfessionalTeacherProfessionalStudent(Model $model,int $evaluate){
        switch ($evaluate) {
            default:
                throw new ClassifyException( 'Configuration error',
                    self::MESSAGE_SUPPORT,
                    9000 );
        }

    }


    protected function AmateurTeacher(Model $model, int $evaluate){
            switch($model->getName()){
                default:
                    throw new ClassifyException('Configuration error',
                        self::MESSAGE_SUPPORT,
                        9000);
        }

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
        return $p1->getTypeB()->getName()=='student'?$p1:$p2;
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
    private function highModel($models):Model
    {
        $collection=array_values($models);
        switch(count($collection)) {
            case 1:
                /** @var Model $m1 */
                list($m1)=$collection;
                return $m1;
            case 2:
                /**
                 * @var Model $m2
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
    }

    private function istdAmateurStudentAmateurStudentProficiency(Value $genre, Participant $p1,Participant $p2):Value
    {
        /** @var Value $p1Proficiency */
        $p1Proficiency=$p1->fetchGenreProficiency($genre);
        /** @var Value $p2Proficiency */
        $p2Proficiency=$p2->fetchGenreProficiency($genre);


        $p1Models =$p1->getModels()->toArray();
        $p2Models = $p2->getModels()->toArray();


        $p1HighModel = $this->highModel($p1Models);
        $p2HighModel = $this->highModel($p2Models);

        switch($p1HighModel->getName())
        {
            case 'ISTD Medal Exams':
                switch($p2HighModel->getName())
                {
                    case 'ISTD Medal Exams':
                        $name=self::HIGHER_PROFICIENCY_MEDAL[$p1Proficiency->getName()][$p2Proficiency->getName()];
                        return $this->domainValueHash['proficiency'][$name];
                    case 'Georgia DanceSport Amateur':
                        //TODO: Convert p2 from Amateur Proficiency to ISTD Proficiency

                        //TODO: return the higher proficiency
                    case 'Georgia DanceSport ProAm':
                        //TODO: Convert p2 from ProAm Proficiency to Amateur Proficiency to ISTD Proficiency
                        //TODO: return the higher ISTD proficiency
                }

            case 'Georgia DanceSport Amateur':
                //TODO: Convert p1 from Amateur Proficiency to ISTD Proficiency
                switch($p2HighModel->getName())
                {
                    case 'ISTD Medal Exams':
                        //TODO: return the higher proficiency
                    case 'Georgia DanceSport Amateur':
                        //TODO: Convert p2 from Amateur Proficiency to ISTD Proficiency
                        //TODO: return the higher proficiency
                    case 'Georgia DanceSport ProAm':
                        //TODO: Convert p2 from ProAm Proficiency to Amateur Proficiency to ISTD Proficiency
                        //TODO: return the higher ISTD proficiency
                }


            case 'Georgia DanceSport ProAm':
                //TODO: Convert p1 from ProAm Proficiency to Amateur Proficiency to ISTD Proficiency
                switch($p2HighModel->getName())
                {
                    case 'ISTD Medal Exams':
                        //TODO: return the higher proficiency
                    case 'Georgia DanceSport Amateur':
                        //TODO: Convert p2 from Amateur Proficiency to ISTD Proficiency
                        //TODO: return the higher proficiency
                    case 'Georgia DanceSport ProAm':
                        //TODO: Convert p1 from ProAm Proficiency to Amateur Proficiency to ISTD Proficiency
                        //TODO: Convert p2 from ProAm Proficiency to Amateur Proficiency to ISTD Proficiency
                        //TODO: return the higher ISTD proficiency
                }

        }
    }

    private function istdAmateurStudentProficiency(Value $genre, Participant $p)
    {
        $proficiency=$p->fetchGenreProficiency($genre);
        $models = $p->getModels();
        $highModel = $this->highModel($models);
        switch($highModel->getName())
        {
            case 'ISTD Medal Exams':
                return $proficiency;
            case 'Georgia DanceSport Amateur':
                //TODO: Convert from Amateur Proficiency to ISTD Proficiency

                //TODO: return ISTD Proficiency
            case 'Georgia DanceSport ProAm':
                //TODO: Convert from ProAm Proficiency to Amateur Proficiency to ISTD Proficiency
                //TODO: return ISTD Proficiency
        }

    }


    /**
     * @param Value $genre
     * @param Participant $p1
     * @param Participant $p2
     * @return Value
     */
    private function gadsamAmateurStudentAmateurStudentProficiency(Value $genre, Participant $p1,Participant $p2):Value
    {
        /** @var Value $p1Proficiency */
        $p1Proficiency=$p1->fetchGenreProficiency($genre);
        /** @var Value $p2Proficiency */
        $p2Proficiency=$p2->fetchGenreProficiency($genre);


        $p1Models = $p1->getModels()->toArray();
        $p2Models = $p2->getModels()->toArray();


        $p1HighModel = $this->highModel($p1Models);
        $p2HighModel = $this->highModel($p2Models);
        switch($p1HighModel->getName())
        {
            case 'Georgia DanceSport Amateur':
                switch($p2HighModel->getName())
                {
                    case 'Georgia DanceSport Amateur':
                        $name = self::HIGHER_PROFICIENCY_AMATEUR[$p1Proficiency->getName()][$p2Proficiency->getName()];
                        return $this->domainValueHash['proficiency'][$name];
                    case 'Georgia DanceSport ProAm':

                        $p2AmateurProficiency = self::PROFICIENCY_STUDENT_AMATEUR[$p2Proficiency->getName()];
                        $name = self::HIGHER_PROFICIENCY_AMATEUR[$p1Proficiency->getName()][$p2AmateurProficiency];
                        return $this->domainValueHash['proficiency'][$name];
                }
                break;
            case 'Georgia DanceSport ProAm':
                $p1AmateurProficiency = self::PROFICIENCY_STUDENT_AMATEUR[$p1Proficiency->getName()];
                switch($p2HighModel->getName())
                {
                    case 'Georgia DanceSport Amateur':
                        $name = self::HIGHER_PROFICIENCY_AMATEUR[$p1AmateurProficiency][$p2Proficiency->getName()];
                        return $this->domainValueHash['proficiency'][$name];
                    case 'Georgia DanceSport ProAm':
                        $p2AmateurProficiency = self::PROFICIENCY_STUDENT_AMATEUR[$p2Proficiency->getName()];
                        $name = self::HIGHER_PROFICIENCY_AMATEUR[$p1AmateurProficiency][$p2AmateurProficiency];
                        return $this->domainValueHash['proficiency'][$name];

                }
        }
    }

    /**
     * @param Value $genre
     * @param Participant $p
     * @return Value
     */
    private function gadsamAmateurStudentProficiency(Value $genre, Participant $p):Value
    {
        /** @var Value $proficiency */
        $proficiency=$p->fetchGenreProficiency($genre);
        $models = $p->getModels();
        $highModel = $this->highModel($models);
        switch($highModel->getName())
        {
            case 'Georgia DanceSport Amateur':
                return $proficiency;
            case 'Georgia DanceSport ProAm':
                $name = self::PROFICIENCY_STUDENT_AMATEUR[$proficiency->getName()];
                return $this->domainValueHash['proficiency'][$name];
        }

    }


    /**
     * @param Value $genre
     * @param Participant $p1
     * @param Participant $p2
     * @return Value
     * @throws ClassifyException
     */
    private function gadsproamTeacherStudentProficiency(Value $genre, Participant $p1, Participant $p2):Value
    {
        $student = $this->student($p1,$p2);
        $models=$student->getModels();
        $highModel=$this->highModel($models->toArray());
        if($highModel->getName()!='Georgia DanceSport ProAm')
        {   $name=$student->getName();
            throw new ClassifyException('Invalid Competition Model',"configuration error for $name",9000);
        }
        return $student->fetchGenreProficiency($genre);

    }


    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Value
     * @throws ClassifyException
     */
    private function istdAmateurStudentAmateurStudentAge(Participant $p1,Participant $p2): ?Value
    {
        $elder=$this->elder($p1,$p2);
        $elderYears = $elder->getYears();
        $younger=$this->younger($p1,$p2);
        $youngerYears = $younger->getYears();
        foreach(self::AGE_EXAMS as $ageBreak=>$name) {
            if($elderYears<16 && $elderYears>=$ageBreak) {
                return $this->domainValueHash['age'][self::AGE_EXAMS[$ageBreak]];
            }
            if ($youngerYears<16 && $elderYears>=16 && $elderYears>=$ageBreak) {
               return $this->domainValueHash['age'][self::AGE_EXAMS[$ageBreak]];
            }
            if ($youngerYears>=$ageBreak) {
                return $this->domainValueHash['age'][self::AGE_EXAMS[$ageBreak]];
            }
        }
        $name = $p1->getName().' & '.$p2->getName();
        throw new ClassifyException('iSTD Age',"Unable to classify age for $name",9000);
    }

    /**
     * @param Participant $p
     * @return Value|null
     * @throws ClassifyException
     */
    private function istdAmateurStudentAge(Participant $p): ?Value
    {
        $years = $p->getYears();
        foreach(self::AGE_EXAMS as $ageBreak=>$name){
            if($years>=$ageBreak) {
                return $this->domainValueHash['age'][self::AGE_EXAMS[$ageBreak]];
            }
        }
        $name = $p->getName();
        throw new ClassifyException("ISTD Age", "Unable to classify age for $name", 9000);
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Value|null
     * @throws ClassifyException
     */
    private function gadsamAmateurStudentAmateurStudentAge(Participant $p1,Participant $p2): ?Value
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

    /**
     * @param Participant $p
     * @return Value|null
     * @throws ClassifyException
     */
    private function gadsamAmateurStudentAge(Participant $p): ?Value
    {
        $years = $p->getYears();
        foreach(self::AGE_STUDENT as $ageBreak=>$name){
            if($years>=$ageBreak) {
                return $this->domainValueHash['age'][self::AGE_STUDENT[$ageBreak]];
            }
        }
        $name = $p->getName();
        throw new ClassifyException("Georgia DanceSport Amateur Age", "Unable to classify age for $name", 9000);
    }


    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Value|null
     * @throws ClassifyException
     */
    private function gadsproamTeacherStudentAge(Participant $p1, Participant $p2) : ?Value
    {
        $student=$this->student($p1,$p2);
        $years = $student->getYears();
        foreach(self::AGE_STUDENT as $ageBreak=>$ageName) {
            if($years>=$ageBreak) {
                $value=$this->domainValueHash['age'][self::AGE_STUDENT[$ageBreak]];
                return $value;
            }
        }
        $name = $p1->getName().' & '.$p2->getName();
        throw new ClassifyException("Georgia DanceSport Amateur Age", "Unable to classify age for $name", 9000);
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Player
     */
    public function couple(Participant $p1, Participant $p2) : Player
    {
        /** @var ArrayCollection $commonGenreValues */
        $commonModels = $this->commonModels( $p1, $p2 );
        $p1TypeA = $p1->getTypeA()->getName();
        $p1TypeB = $p1->getTypeB()->getName();
        $p2TypeA = $p2->getTypeA()->getName();
        $p2TypeB = $p2->getTypeB()->getName();
        $classifier = $p1TypeA . $p1TypeB . $p2TypeA . $p2TypeB;
        $player = new Player();
        $player->addParticipant( $p1 )
            ->addParticipant( $p2 );
        foreach ($commonModels as $modelName => $model) {
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
            while ($genre = $iterator->current()) {
                /** @var Value $proficiency */
                $proficiency = $proficiencyFn( $genre, $p1, $p2 );
                $qualification = new Qualification();
                $qualification->set( [$genre, $proficiency, $age, $type] );
                $player->addQualification( $model, $qualification );
                $iterator->next();
            }
        }
        return $player;
    }

    /**
     * @param Participant $p
     * @return Player
     */
    public function solo(Participant $p) : Player
    {
        $models = $p->getModels()->toArray();
        $pTypeA = $p->getTypeA()->getName();
        $pTypeB = $p->getTypeB()->getName();
        $classifier = $pTypeA.$pTypeB;
        $player = new Player();
        $player->addParticipant($p);
        foreach($models as $modelName=>$model)
        {
            $proficiencyFn = $this->$classifier( $model, self::PROFICIENCY );
            $ageFn = $this->$classifier( $model, self::AGE );
            $typeFn = $this->$classifier( $model, self::TYPE );
            $names=$p->fetchGenreNames();
            foreach($names as $name) {
                $genre=isset($this->domainValueHash['style'][$name])?
                            $this->domainValueHash['style'][$name]:
                            $this->domainValueHash['substyle'][$name];
                $proficiency=$proficiencyFn($genre,$p);
                $age = $ageFn($p);
                $type= $typeFn($p);
                $qualification = new Qualification();
                $qualification->set([$genre,$proficiency,$age,$type]);
                $player->addQualification($model,$qualification);
            }
        }
        return $player;
    }
}