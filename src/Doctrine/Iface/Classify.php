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

namespace App\Doctrine\Iface;

use App\Entity\Competition\Competition;
use App\Entity\Competition\Iface;
use App\Entity\Models\Value;
use App\Entity\Sales\Client\ClientException;
use App\Entity\Sales\Client\Participant;
use App\Entity\Sales\Client\Player;
use App\Entity\Sales\Client\Qualification;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\IfaceRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;


class Classify
{

    /**
     * @var CompetitionRepository
     */
    private $competitionRepository;
    /**
     * @var ModelRepository
     */
    private $modelRepository;

    /**
     * @var IfaceRepository
     */
    private $ifaceRepository;

    private $domainValueHash;

    private $ageMappings;

    private $proficiencyMappings;

    private $coupleClassifyA = ['Professional'=>['Professional'=>'Professional',
                                                 'Amateur'=>'Teacher-Student'],
                                'Amateur'=>['Professional'=>'Teacher-Student',
                                            'Amateur'=>'Amateur']];
    private $coupleClassifyB = ['Professional'=> ['Teacher'=>['Teacher'=>'Professional',
                                                              'Student'=>null],
                                                  'Student'=>null],
                                'Amateur'=>  ['Teacher'=>['Teacher'=>'Amateur',
                                                          'Student'=>'Teacher-Student'],
                                              'Student'=>['Teacher'=>'Teacher-Student',
                                                          'Student'=>'Amateur']],
                                'Teacher-Student'=>['Teacher'=>['Teacher'=>null,
                                                                'Student'=>'Teacher-Student']],
                                                    'Student'=>['Teacher'=>'Teacher-Student',
                                                                'Student'=>null]];

    private $ageMatrix = [75=>[70=>'Senior 5',
                               60=>'Senior 4',
                               50=>'Senior 3',
                               40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult'],
                          65=>[60=>'Senior 4',
                               50=>'Senior 3',
                               40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult'],
                          55=>[50=>'Senior 3',
                               40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult'],
                          45=>[40=>'Senior 2',
                               30=>'Senior 1',
                               19=>'Adult'],
                          35=> [30=>'Senior 1',
                                19=>'Adult'],
                          19=> [19=>'Adult']];


    private $valueById;

    public function __construct(
        CompetitionRepository $competitionRepository,
        ModelRepository $modelRepository,
        IfaceRepository $ifaceRepository,
        ValueRepository $valueRepository
    )
    {
        $this->competitionRepository = $competitionRepository;
        $this->modelRepository = $modelRepository;
        $this->ifaceRepository = $ifaceRepository;
        $this->domainValueHash = $valueRepository->fetchDomainValueHash();
        $this->valueById = $valueRepository->fetchAllValuesById();
    }

    /**
     * @param Competition $competition
     */
    public function setCompetition(Competition $competition)
    {
        /** @var Iface $iface */
        $iface = $this->ifaceRepository->findOneBy(['competition'=>$competition]);
        $mapping = $iface->getMapping();
        $this->ageMappings = $mapping['age'];
        $this->proficiencyMappings = $mapping['proficiency'];
    }


    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Value
     * @throws ClassifyException
     */
    private function coupleType(Participant $p1, Participant $p2):Value
    {
        $t1A = $p1->getTypeA();
        $t2A = $p2->getTypeA();
        $t1B = $p1->getTypeB();
        $t2B = $p2->getTypeB();
        $t1BName = $t1B->getName();
        $t2BName = $t2B->getName();
        $coupleClassifyA = $this->coupleClassifyA[$t1A->getName()][$t2A->getName()];
        $coupleClassifyB = $this->coupleClassifyB[$coupleClassifyA][$t1BName][$t2BName];
        if(is_null($coupleClassifyB)) {
            $name1 = $p1->getName();
            $name2 = $p2->getName();
            $message = sprintf("Unable to classify %s and %s for types %s and %s",
                                $name1,$name2, $t1BName,$t2BName);
            throw new ClassifyException('Classification error.',$message,9000);
        }
        if(!isset($this->domainValueHash['type'][$coupleClassifyB])) {
            throw new ClassifyException('Type not found',"No value for \"$coupleClassifyB\"",9000);
        }
        return $this->domainValueHash['type'][$coupleClassifyB];
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Participant
     */
    private function elder(Participant $p1, Participant $p2):Participant
    {
        return $p1->getYears()>=$p2->getYears()?$p1:$p2;
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Participant
     */
    private function younger(Participant $p1, Participant $p2):Participant
    {
        return $p1->getYears()<$p2->getYears()?$p1:$p2;
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Participant
     * @throws ClassifyException
     */
    private function student(Participant $p1, Participant $p2):Participant
    {
        if($p1->getTypeB()->getName()=='Student') {
            return $p1;
        }
        if($p2->getTypeB()->getName()=='Student') {
            return $p2;
        }
        $p1Name = $p1->getName();
        $p2Name = $p2->getName();
        throw new ClassifyException("Classification error.",
            "Unable to classify $p1Name or $p2Name as teacher or student.", 9000);
    }


    /**
     * @param Participant $elder
     * @param Participant $younger
     * @return Value
     * @throws ClassifyException
     */
    private function ageAmateurAdultCouple(Participant $elder, Participant $younger):Value
    {
        $elderAge = $elder->getYears();
        $youngerAge = $younger->getYears();
        foreach($this->ageMatrix as $elderAgeBreak=>$youngerSubmatrix){
            if($elderAge>=$elderAgeBreak) {
                foreach($youngerSubmatrix as $youngerAgeBreak=>$classification){
                    if($youngerAge>=$youngerAgeBreak){
                        if(!isset($this->domainValueHash['age'][$classification])){
                            throw new ClassifyException("$classification","does not exist.",9000);
                        }
                        return $this->domainValueHash['age'][$classification];
                    }
                }
            }
        }
        $message = 'Failed to classify age for '.$elder->getName().' and '.$younger->getName();
        throw new ClassifyException("Age classification failure", $message, 9000);
    }


    /**
     * @param Participant $p1
     * @param Participant $p2
     * @param Value $type
     * @return Value|null
     * @throws ClassifyException
     */
    private function coupleAge(Participant $p1, Participant $p2, Value $type):?Value
    {
        $elder = $this->elder($p1,$p2);
        $younger = $this->younger($p1,$p2);
        $elderAge = $elder->getYears();
        $youngerAge = $younger->getYears();
        $mappings = $this->ageMappings[$type->getName()];
        $typeName = $type->getName();
        if($elderAge-$youngerAge>=40 && $youngerAge<19){
            switch($typeName){
                case 'Amateur':
                    return $this->domainValueHash['age']['Senior Youngster'];
                case 'Teacher Student':
                    /** @var Value $value */
                    $value = $this->valueById[$mappings[$youngerAge]];
                    if($value->getDomain()->getName()<>'age') {
                        $valueId = $mappings[$youngerAge];
                        throw new ClassifyException("Age classification error.",
                            "$valueId does not correspond to an age value.",9000);
                    }
                    return $value;
                default:
                    throw new ClassifyException("Classification failure",
                        "\"$typeName\" found.  Should be \"Amateur\" or \"Teacher Student\"",9000);
            }
        }
        if($elderAge-$youngerAge>=20 && $youngerAge<19){
            switch($typeName){
                case 'Amateur':
                    return $this->domainValueHash['age']['Adult Youngster'];
                case 'Teacher Student':
                    /** @var Value $value */
                    $value = $this->valueById[$mappings[$youngerAge]];
                    if($value->getDomain()->getName()<>'age') {
                        $valueId = $mappings[$youngerAge];
                        throw new ClassifyException("Age classification error.",
                            "$valueId does not correspond to an age value.",9000);
                    }
                    return $value;
                default:
                    throw new ClassifyException("Age classification error.",
                        "expected Amateur or Teacher Student.",9000);

            }
        }
        if($elderAge<19 && $youngerAge<19) {
            $ageId = $mappings[$elderAge];
            /** @var Value $value */
            $value = $this->valueById[$ageId];
            if($value->getDomain()->getName()!='age'){
                throw new ClassifyException("Age classification error.".
                    "Cannot locate age value for id=$ageId",9000);
            }
            return $value;
        }

        if($elderAge>=19 && $youngerAge<19 && $type->getName()=='Amateur'){
            $value = $this->domainValueHash['age']['Adult'];
            return $value;
        }

        if($elderAge>=19 && $youngerAge>=19){
            switch($typeName) {
                case 'Amateur':
                    return $this->ageAmateurAdultCouple($elder,$younger);
                case 'Teacher Student':
                    $student = $this->student($p1,$p2);
                    $years = $student->getYears();
                    $value = $this->valueById[$this->ageMappings[$years]];
                    return $value;
                case 'Professional':
                    return null;
                default:
                    throw new ClassifyException("Type missing.",'  Expected "Amateur" or "Teacher Student"',9000);
            }
        }
        $message=sprintf('Failed to consider age for %s and %s',$p1->getName(),$p2->getName());
        //var_dump($p1->getTypeA()->getName(), $p1->getTypeB()->getName(),$type->getName(),$type->getName());die;
        throw new ClassifyException("Age",$message, 9000);
    }

    /**
     * @param int $genre
     * @param Participant $p1
     * @param Participant $p2
     * @param Value $type
     * @return Value
     * @throws ClassifyException
     */
    private function coupleProficiency(int $genre, Participant $p1,Participant $p2,Value $type):Value
    {
        switch($type->getName()){
            case 'Amateur':
                $p1Proficiency=$p1->getGenreProficiency($genre);
                $p2Proficiency=$p2->getGenreProficiency($genre);
                $studentProficiencies = array_keys($this->proficiencyMappings['Student-Amateur']);
                $isStudentProficiencyP1=in_array($p1Proficiency,$studentProficiencies);
                $isStudentProficiencyP2=in_array($p2Proficiency,$studentProficiencies);
                $p1AmateurProficiency=$isStudentProficiencyP1?
                    $this->proficiencyMappings['Student-Amateur'][$p1Proficiency]:
                    $p1Proficiency;
                $p2AmateurProficiency=$isStudentProficiencyP2?
                    $this->proficiencyMappings['Student-Amateur'][$p2Proficiency]:
                    $p2Proficiency;
                $coupleProficiency = $p1AmateurProficiency>$p2AmateurProficiency?
                    $p1AmateurProficiency:$p2AmateurProficiency;
                /** @var Value $proficiencyValue */
                $proficiencyValue = $this->valueById[$coupleProficiency];
                if($proficiencyValue->getDomain()->getName()!='proficiency') {
                    $message = sprintf('Misclassification error for %s or %s', $p1->getName(),$p2->getName());
                    throw new ClassifyException('Classification error',$message,9000);
                }
                return $proficiencyValue;
            case 'Teacher-Student':
                $student = $this->student($p1,$p2);
                $proficiency = $student->getGenreProficiency($genre);
                /** @var Value $proficiencyValue */
                $proficiencyValue = $this->valueById[$proficiency];
                if($proficiencyValue->getDomain()->getName()!='proficiency'){
                    $message = sprintf( 'Misclassification error for %s or %s', $p1->getName(), $p2->getName() );
                    throw new ClassifyException( 'Classification error', $message, 9000 );
                }
                return $proficiencyValue;
            default:
                $message = sprintf('"%s" not found. Expected "Amateur" or "Teacher-Student"');
                throw new ClassifyException('Classification error',$message);
        }
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return Player
     * @throws ClassifyException
     * @throws ClientException
     */
    public function couple(Participant $p1, Participant $p2) : Player
    {
        $genres = array_intersect(array_keys($p1->getGenreProficiency()->toArray()),
                                  array_keys($p1->getGenreProficiency()->toArray()));
        $typeValue = $this->coupleType($p1,$p2);
        $ageValue  = $this->coupleAge($p1,$p2,$typeValue);
        $player = new Player();
        $player->addParticipant($p1);
        $player->addParticipant($p2);
        foreach($genres as $genre) {
            /** @var Value $genreValue */
            $genreValue = $this->valueById[$genre];
            $genreName  = $genreValue->getName();
            if(!isset($this->domainValueHash['style'][$genreName]) &&
                !isset($this->domainValueHash['substyle'][$genreName])) {
                $message = sprintf('Not found for %s and %s',$p1->getName(),$p2->getName());
                throw new ClassifyException("$genreName",$message,9000);
            }
            $proficiencyValue = $this->coupleProficiency($genre,$p1,$p2,$typeValue);
            $qualification = new Qualification();
            $qualification->set([$genreValue,$proficiencyValue,$ageValue,$typeValue]);
            $player->addQualification($qualification);
        }
        return $player;
    }

    /**
     * @param Participant $p
     * @return Player
     * @throws ClassifyException
     * @throws ClientException
     */
    public function solo(Participant $p) : Player
    {
        $genres = array_keys($p->getGenreProficiency()->toArray());
        $player = new Player();
        $player->addParticipant($p);
        $typeA = $p->getTypeA();
        if($typeA->getName()=='Professional'){
            throw new ClassifyException('Invalid','No solo events for professionals',9000);
        }
        foreach($genres as $genre) {
            /** @var Value $genreValue */
            $genreValue = $this->valueById[$genre];
            $domainName = $genreValue->getDomain()->getName();
            if($domainName!='style' && $domainName!='substyle') {
                throw new ClassifyException('Genre Error',
                    "ID=$genre does not correspond to a style or substyle for ".$p->getName(),
                    9000);
            }
            $typeValue = $p->getTypeA();
            if($typeValue->getDomain()->getName()!='type') {
                throw new ClassifyException("Type Error",
                    " does not correspond to a type for ".$p->getName(),9000);
            }
            if($typeValue->getName()!='Amateur'){
                throw new ClassifyException("Type Error",
                    $p->getName().' must be classified as "Amateur" for solo events.',9000);
            }
            $proficiency = $p->getGenreProficiency($genre);
            $mapping = $this->proficiencyMappings['Student-Amateur'];
            $isStudentProficiency=in_array($proficiency,array_keys($mapping));
            $amateurProficiencyId=$isStudentProficiency?$mapping[$proficiency]:$proficiency;
            /** @var Value $proficiencyValue */
            $proficiencyValue = $this->valueById[$amateurProficiencyId];
            $ageValue = $this->valueById[$this->ageMappings['Amateur'][$p->getYears()]];
            if($proficiencyValue->getDomain()->getName()!='proficiency') {
                $proficiencyId = $proficiencyValue->getId();
                throw new ClassifyException('Proficiency',
                        "ID=$proficiencyId does not correspond to proficiency for ".$p->getName());
            }
            $qualification = new Qualification();
            $qualification->set([$genreValue,$proficiencyValue,$ageValue,$typeValue]);
            $player->addQualification($qualification);
        }
        return $player;
    }
}