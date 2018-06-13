<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/9/18
 * Time: 11:51 AM
 */

namespace App\Tests\Doctrine\Iface;


use App\Entity\Competition\Competition;
use App\Entity\Models\Value;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Contact;
use App\Entity\Sales\Iface\Participant;
use App\Entity\Sales\Iface\Qualification;
use App\Entity\Sales\Workarea;
use App\Exceptions\GeneralException;
use App\Exceptions\MissingException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\ContactRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\WorkareaRepository;


class EntriesAssessGenerator extends BaseParser
{
    const YEARS = ['Baby'=>3,
                   'Juvenile'=>5,
                   'Preteen 1'=>8,
                   'Preteen 2'=>10,
                   'Junior 1'=>12,
                   'Junior 2'=>14,
                   'Youth'=>17,
                   'Adult'=>30,
                   'Senior 1'=>40,
                   'Senior 2'=>50,
                   'Senior 3'=>60,
                   'Senior 4'=>70,
                   'Senior 5'=>80,
                   'Under 6'=>4,
                   'Under 8'=>7,
                   'Under 12'=>11,
                   'Junior 12-16'=>14,
                   'Adult 16-50'=>30,
                   'Senior 50'=>60];


    /** @var Channel */
    private $channel;

    /** @var Competition */
    private $competition;
    /**
     * @var ChannelRepository
     */
    private $channelRepository;
    /**
     * @var ContactRepository
     */
    private $contactRepository;
    /**
     * @var WorkareaRepository
     */
    private $workareaRepository;
    /**
     * @var FormRepository
     */
    private $formRepository;
    /**
     * @var TagRepository
     */
    private $tagRepository;

    private $contactCount=0;

    private $contactParticipantPlayer =[];

    /**
     * EntriesAssessGenerator constructor.
     * @param ChannelRepository $channelRepository
     * @param ContactRepository $contactRepository
     * @param WorkareaRepository $workareaRepository
     * @param FormRepository $formRepository
     * @param TagRepository $tagRepository
     * @param CompetitionRepository $competitionRepository
     * @param ModelRepository $modelRepository
     * @param ValueRepository $valueRepository
     */
    public function __construct(
       ChannelRepository $channelRepository,
       ContactRepository $contactRepository,
       WorkareaRepository $workareaRepository,
       FormRepository $formRepository,
       TagRepository $tagRepository,
       CompetitionRepository $competitionRepository,
       ModelRepository $modelRepository,
       ValueRepository $valueRepository)
   {
       parent::__construct( $competitionRepository, $modelRepository, $valueRepository);
       $this->channelRepository = $channelRepository;
       $this->contactRepository = $contactRepository;
       $this->workareaRepository = $workareaRepository;
       $this->formRepository = $formRepository;
       $this->tagRepository = $tagRepository;
       $this->modelRepository=$modelRepository;
   }

    /**
     * @param string $yaml
     * @throws GeneralException
     * @throws MissingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function parse(string $yaml)
    {
        $r = $this->fetchPhpArray( $yaml );
        if (key( $r['data'] ) == 'comment') {
            next( $r['data'] );
            next( $r['position'] );
        }
        list($channelName,$channelPosition,$channelKey,$channelKeyPosition)
            = $this->current( $r['data'], $r['position'] );
        $this->channel = $this->fetchChannel($channelName,$channelPosition,$channelKey,$channelKeyPosition);
        list($competitionName,$competitionPosition,$competitionKey,$competitionKeyPosition)
            = $this->next($r['data'],$r['position']);
        $this->competition=$this->fetchCompetition($competitionName,$competitionPosition,
                                                    $competitionKey,$competitionKeyPosition);
        list($data,$dataPosition,$key,$keyPosition)
            = $this->next($r['data'],$r['position']);
        $this->buildParticipation($data,$dataPosition,$key,$keyPosition);
    }


    /**
     * @param string $name
     * @param string $namePosition
     * @param string $key
     * @param string $keyPosition
     * @return null|object
     * @throws GeneralException
     */
    private function fetchChannel(string $name,string $namePosition, string $key,string $keyPosition)
    {
        if($key != 'channel') {
            throw new GeneralException($key, $keyPosition, 'expected "channel"',
                EntriesAssessExceptionCode::CHANNEL);
        }
        $channel=$this->channelRepository->findOneBy(['name'=>$name]);
        if(!$channel){
            throw new GeneralException($name, $namePosition, "does not exist",
                EntriesAssessExceptionCode::INVALID_CHANNEL);
        }
        return $channel;
    }

    /**
     * @param $data
     * @param $position
     * @param $key
     * @param $keyPosition
     * @throws GeneralException
     * @throws MissingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildParticipation($data,$position,$key,$keyPosition)
    {
        if($key != 'participation') {
            throw new GeneralException($key, $keyPosition, 'expected "participation"',
                EntriesAssessExceptionCode::PARTICIPATION);
        }
        list($dataPart, $positionPart, , ) = $this->current($data,$position);
        while($dataPart) {
            $this->buildParticipationParts($dataPart,$positionPart);
            list($dataPart, $positionPart, , )=$this->next($data,$position);
        }
    }

    /**
     * @param $data
     * @param $position
     * @throws GeneralException
     * @throws MissingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildParticipationParts($data,$position)
    {
        $validKeys = ['model','contact','genre','lead','follow','events'];
        $listStr = join('","',$validKeys);
        $positionData=[];
        list($dataPart,$dataPosition,$key,$keyPosition)
            = $this->current($data,$position);
        while($dataPart) {
            if(!in_array($key,$validKeys)) {
                throw new GeneralException($key,$keyPosition,"expected \"$listStr\"",
                        EntriesAssessExceptionCode::PARTICIPATION_KEYS);
            }
            $positionData[$key]=$dataPosition;
            list($dataPart, $dataPosition,$key,$keyPosition)
                = $this->next($data,$position);
        }
        reset($data);reset($positionData);
        $diff=array_diff($validKeys,array_keys($positionData));
        if(count($diff)){
            $allKeys = array_keys($data);
            $locations = array_keys($position);
            throw new MissingException($diff,$locations,EntriesAssessExceptionCode::MISSING_PARTICIPATION_KEYS);
        }
        $result=$this->buildContactPlayers($data,$positionData);
    }

    /**
     * @param $data
     * @param $position
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildContactPlayers($data,$position) {

        $participants = [];
        $model = $this->chooseModel($data,$position);
        $contact = $this->buildContact($data,$position);
        $leader = $this->buildLeader($model->getId(),$data,$position);
        $followers=$this->buildFollowers($model->getId(),$data,$position);

        //TODO: pair followers with contact and add to the database

    }

    private function chooseModel($data,$position) {
        $modelName=$data['model'];
        $modelPosition=$position['model'];
        $model=$this->modelRepository->findOneBy(['name'=>$modelName]);
        if(is_null($model)) {
            throw new GeneralException($modelName,$modelPosition,"is invalid",
                    EntriesAssessExceptionCode::INVALID_MODEL);
        }
        return $model;
    }

    /**
     * @param array $data
     * @param array $position
     * @return Contact
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildContact(array $data,array $position)
    {
        list($first,,$firstKey,$firstKeyPosition)
            =$this->current($data['contact'],$position['contact']);
        if($firstKey!='first') {
            throw new GeneralException($firstKey,$firstKeyPosition,'expected "first"',
                EntriesAssessExceptionCode::KEY_FIRST);
        }
        list($last,,$lastKey,$lastKeyPosition)
            =$this->next($data['contact'],$position['contact']);
        if($lastKey!='last') {
            throw new GeneralException($lastKey,$lastKeyPosition,'expected "first"',
                EntriesAssessExceptionCode::KEY_LAST);
        }
        $tag = $this->tagRepository->fetch('competition');
        $workarea = new Workarea();
        $workarea->setChannel($this->channel)
                 ->setTag($tag);
        $em=$this->workareaRepository->getEntityManager();
        $em->persist($workarea);
        $em->flush();
        $this->contactCount++;
        $cnt = str_pad($this->contactCount,'0',STR_PAD_LEFT);
        $contact=new Contact();
        $contact->setFirst(substr($first,0,40))
                ->setLast(substr($last,0,40))
                ->setCity('Sandy Springs')
                ->setSt('GA')
                ->setCountry('United States')
                ->setPhone('(678)235-8395')
                ->setOrganization("Organization".$cnt)
                ->setEmail('mgarber+'.$cnt.'@georgiasport.org');
        $contact->getWorkarea()->add($workarea);
        $em=$this->contactRepository->getEntityManager();
        $em->persist($contact);
        $em->flush();
        return $contact;

    }

    /**
     * @param int $modelId
     * @param array $data
     * @param array $position
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildLeader(int $modelId, array $data,array $position)
    {

        list($proficiency,$proficiencyPosition,$key,$keyPosition)
            =$this->current($data['lead'],$position['lead']);
        if($key!='proficiency') {
            throw new GeneralException($proficiency,$keyPosition,'expected "proficiency"',
                EntriesAssessExceptionCode::PROFICIENCY);
        }
        if(!$this->hasDomainValue('proficiency',$proficiency)) {
            throw new GeneralException($proficiency,$proficiencyPosition,"is invalid",
                        EntriesAssessExceptionCode::INVALID_PROFICIENCY);
        }
        list($age,$agePosition,$key,$keyPosition)=$this->next($data['lead'],$position['lead']);
        if($key!='age') {
            throw new GeneralException($key,$keyPosition,'expected age',
                        EntriesAssessExceptionCode::AGE);
        }
        if(!$this->hasDomainValue('age',$age)) {
            throw new GeneralException($age,$agePosition,"is invalid",
                        EntriesAssessExceptionCode::INVALID_AGE);
        }
        list($type,$typePosition,$key,$keyPosition)=$this->next($data['lead'],$position['lead']);
        if($key!='type') {
            throw new GeneralException($key,$keyPosition,'expected "type"',
                            EntriesAssessExceptionCode::TYPE);
        }
        if(!$this->hasDomainValue('type',$type)) {
            throw new GeneralException($type,$typePosition,"is invalid",
                                EntriesAssessExceptionCode::INVALID_TYPE);
        }
        $tag = $this->tagRepository->fetch('participant');
        $genre = $data['genre'];
        //$qualification = $this->buildQualification($genre,$proficiency,$age,$type);
        $leader = new Participant($this->valueById,
                                  $this->modelRepository->getModelById(),
                                  $this->formRepository,
                                  $tag);
        /** @var Value $genreValue */
        $genreValue = isset($this->domainValueHash['style'][$genre])?
                        $this->domainValueHash['style'][$genre]:
                        $this->domainValueHash['substyle'][$genre];
        /** @var Value $proficiencyValue */
        $proficiencyValue = $this->domainValueHash['proficiency'][$proficiency];
        $years = self::YEARS[$age];
        $typeA = $this->domainValueHash['proficiency'][$proficiency]=='Professional'?
                 $this->domainValueHash['type']['Professional']:
                 $this->domainValueHash['type']['Amateur'];
        $typeB = $this->domainValueHash['type'][$type];
        $leader->setFirst($genre.'-'.$proficiency)
                ->setLast($age.'-'.$type.'-'.'M')
                ->setSex('M')
                ->setTypeA($typeA->getId())
                ->setTypeB($typeB->getId())
                ->addModel($modelId);
        switch($typeA->getName()){
            case 'Amateur':
                switch($typeB->getName()){
                    case 'Student':
                        $leader->setYears(self::YEARS[$age])
                            ->addGenreProficiency($genreValue->getId(),$proficiencyValue->getId());
                }
                break;
        }
    }

    /**
     * @param string $genre
     * @param string $proficiency
     * @param string $age
     * @param string $type
     * @return Qualification
     */
    private function buildQualification(string $genre, string $proficiency, string $age, string $type) {
        $genreValue = isset($this->domainValueHash['style'][$genre])?
            $this->domainValueHash['style'][$genre]:
            $this->domainValueHash['substyle'][$genre];
        $proficiencyValue = $this->domainValueHash['proficiency'][$proficiency];
        $ageValue = $this->domainValueHash['age'][$age];
        $typeValue = $this->domainValueHash['type'][$type];
        $qualification = new Qualification();
        $qualification->set([$genreValue,$proficiencyValue,$ageValue,$typeValue]);
        return $qualification;
    }

    /**
     * @param int $modelId
     * @param array $data
     * @param array $position
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildFollowers(int $modelId, array $data,array $position)
    {
        list($proficiencies,$proficienciesPosition,$key,$keyPosition)
            = $this->current($data['follow'],$position['follow']);
        if($key!='proficiencies') {
                throw new GeneralException($key,$keyPosition,'expected "proficiencies"',
                    EntriesAssessExceptionCode::PROFICIENCIES);
        }
        list($ages,$agesPosition,$key,$keyPosition)
            = $this->next($data['follow'],$position['follow']);
        if($key!='ages') {
            throw new GeneralException($key,$keyPosition,'expected "ages"',
                        EntriesAssessExceptionCode::AGES);
        }
        $this->buildFollowersIteration(
                        $modelId,
                        $data['genre'],
                        $proficiencies,
                        $proficienciesPosition,
                        $ages,
                        $agesPosition,
                        $data['events'],
                        $position['events']);
    }

    /**
     * @param int $modelId
     * @param string $genre
     * @param array $proficiencies
     * @param array $proficienciesPosition
     * @param array $ages
     * @param array $agesPosition
     * @param array $events
     * @param array $eventsPosition
     * @return array
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildFollowersIteration(int $modelId,
                                             string $genre,
                                             array $proficiencies,
                                             array $proficienciesPosition,
                                             array $ages,
                                             array $agesPosition,
                                             array $events,
                                             array $eventsPosition)
    {

        $followers = [];
        list($proficiency,$proficiencyPosition,,)
            =$this->current($proficiencies,$proficienciesPosition);
        while($proficiency) {
            if(!isset($this->domainValueHash['proficiency'][$proficiency])){
                throw new GeneralException($proficiency,$proficiencyPosition,'is invalid',
                    EntriesAssessExceptionCode::INVALID_PROFICIENCY);
            }
            list($age,$agePosition,,)= $this->current($ages,$agesPosition);
            while($age) {
                if(!isset($this->domainValueHash['age'][$age])){
                    throw new GeneralException($age,$agePosition,"is invalid",
                        EntriesAssessExceptionCode::INVALID_AGE);
                }
                $follower = $this->buildSingleFollower($modelId,$genre,$proficiency,$age);
                $selections = $this->eventSelection($events,$eventsPosition);
                $followers[]=['follower'=>$follower,'selections'=>$selections];
                list($age,$agePosition,,) = $this->next($ages,$agesPosition);
            }
            list($proficiency,$proficiencyPosition,,)
                = $this->next($proficiencies,$proficienciesPosition);
        }
        return $followers;
    }

    private function eventSelection($data, $position)
    {
        list($style,$stylePosition,$key,$keyPosition)=
            $this->current($data,$position);
        if($key!='style') {
            throw new GeneralException($key,$keyPosition,'expected "style"',
                        EntriesAssessExceptionCode::STYLE);
        }
        if(!isset($this->domainValueHash['style'][$style])) {
            throw new GeneralException($style,$stylePosition,'is invalid',
                             EntriesAssessExceptionCode::INVALID_STYLE);
        }
        list($type,$typePosition,$key,$keyPosition)
            =$this->next($data,$position);
        if($key!='type') {
            throw new GeneralException($key,$keyPosition,'expected "type"',
                            EntriesAssessExceptionCode::TYPE_EVENT);
        }
        if(!isset($this->domainValueHash['type'][$type])) {
            throw new GeneralException($type,$typePosition,'is invalid',
                                    EntriesAssessExceptionCode::INVALID_TYPE_EVENT);
        }
        list($tag,$tagPosition,$key,$keyPosition)
            =$this->next($data,$position);
        if($key!='tag') {
            throw new GeneralException($key,$keyPosition,'expected "tag"',
                                EntriesAssessExceptionCode::TAG);
        }
        if(!isset($this->domainValueHash['tag'][$tag])) {
            throw new GeneralException($tag,$tagPosition, "is invalid",
                                        EntriesAssessExceptionCode::INVALID_TAG);
        }
        list($chosen,$chosenPosition,$key,$keyPosition)
            =$this->next($data,$position);
        if($key!='chosen') {
            throw new GeneralException($key,$keyPosition,'expected "chosen"',
                                    EntriesAssessExceptionCode::CHOSEN);
        }
        if(!in_array($chosen,['one','all'])){
            throw new GeneralException($chosen,$chosenPosition,'expects "one","all"',
                                EntriesAssessExceptionCode::INVALID_CHOSEN);
        }

        list($assess,$assessPosition,$key,$keyPosition)
            =$this->next($data,$position);
        if($key!='assess') {
            throw new GeneralException($key,$keyPosition,'expected "assess"',
                                   EntriesAssessExceptionCode::ASSESS);
        }
        if(!is_numeric($assess)) {
            throw new GeneralException($assess,$assessPosition,"is invalid",
                            EntriesAssessExceptionCode::INVALID_ASSESS);
        }
        return $data;
    }

    /**
     * @param int $modelId
     * @param string $genre
     * @param string $proficiency
     * @param string $age
     * @return Participant
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildSingleFollower(int $modelId,string $genre,string $proficiency,string $age)
    {
        /** @var Value $genreValue */
        $genreValue = isset($this->domainValueHash['style'][$genre])?
                        $this->domainValueHash['style'][$genre]:
                        $this->domainValueHash['substyle'][$genre];
        /** @var Value $proficiencyValue */
        $proficiencyValue = $this->domainValueHash['proficiency'][$proficiency];
        /** @var Value $typeAValue */
        $typeAValue = $this->domainValueHash['type']['Amateur'];
        /** @var Value $typeBValue */
        $typeBValue = $this->domainValueHash['type']['Student'];
        $follower = new Participant($this->valueById,
                                    $this->modelRepository->getModelById(),
                                    $this->formRepository,
                                    $this->tagRepository->fetch('participant'));

        $follower->setFirst($proficiency)
                    ->setLast($age)
                    ->setSex('F')
                    ->setTypeA($typeAValue->getId())
                    ->setTypeB($typeBValue->getId())
                    ->setYears(self::YEARS[$age])
                    ->addModel($modelId);
        $follower->addGenreProficiency($genreValue->getId(),$proficiencyValue->getId());
        return $follower;
    }
}