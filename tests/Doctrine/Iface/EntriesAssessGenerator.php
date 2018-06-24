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
use App\Entity\Competition\Model;
use App\Entity\Models\Value;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Contact;
use App\Entity\Sales\Iface\Participant;
use App\Entity\Sales\Workarea;
use App\Exceptions\GeneralException;
use App\Exceptions\MissingException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\ContactRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\Iface\ParticipantRepository;
use App\Repository\Sales\Iface\PlayerRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\WorkareaRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;


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

    private $participantCount = 0;

    private $playerCount = 0;

    private $participation = [];


    /**
     * @var Classify
     */
    private $classify;
    /**
     * @var PlayerRepository
     */
    private $playerRepository;
    /**
     * @var ParticipantRepository
     */
    private $participantRepository;

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
     * @param ParticipantRepository $participantRepository
     * @param PlayerRepository $playerRepository
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __construct(
       ChannelRepository $channelRepository,
       ContactRepository $contactRepository,
       WorkareaRepository $workareaRepository,
       FormRepository $formRepository,
       TagRepository $tagRepository,
       CompetitionRepository $competitionRepository,
       ModelRepository $modelRepository,
       ValueRepository $valueRepository,
       ParticipantRepository $participantRepository,
       PlayerRepository $playerRepository)
   {
       parent::__construct( $competitionRepository, $modelRepository, $valueRepository);
       $this->channelRepository = $channelRepository;
       $this->contactRepository = $contactRepository;
       $this->workareaRepository = $workareaRepository;
       $this->formRepository = $formRepository;
       $this->tagRepository = $tagRepository;
       $this->modelRepository=$modelRepository;
       $this->playerRepository = $playerRepository;
       /** @var Channel $channel */
       $channel = $this->channelRepository->findOneBy(['name'=>'Georgia DanceSport']);
       $this->playerRepository->initClassifier($channel);
       $this->participantRepository = $participantRepository;
   }

    /**
     * @param string $yaml
     * @throws GeneralException
     * @throws MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Doctrine\Iface\ClassifyException
     * @throws \Exception
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


    public function getParticipantCount()
    {
        return $this->participantCount;
    }

    public function getPlayerCount()
    {
        return $this->playerCount;
    }

    public function getContactCount()
    {
        return $this->contactCount;
    }

    public function getParticipation()
    {
        return $this->participation;
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
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
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
     * @return void
     * @throws GeneralException
     * @throws MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
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
            $locations = array_keys($position);
            throw new MissingException($diff,$locations,EntriesAssessExceptionCode::MISSING_PARTICIPATION_KEYS);
        }
        $result=$this->buildContactPlayers($data,$positionData);
        return $result;
    }

    /**
     * @param $data
     * @param $position
     * @throws GeneralException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     */
    private function buildContactPlayers($data,$position)
    {
        $model = $this->chooseModel($data,$position);
        $contact = $this->buildContact($data,$position);
        $participation['contact'] = $contact;
        $participation['participants'] = [];
        $participation['players']=[];
        $workarea = $contact->getWorkarea()->first();
        $leader = $this->buildLeader($workarea, $model->getId(),$data,$position);
        $following=$this->buildFollowers($workarea, $model->getId(),$data,$position);
        array_push($participation['participants'],$leader);
        foreach($following as $record) {
            $follower = $record['follower'];
            $selection = $record['selections'];
            array_push($participation['participants'], $follower);
            $player = $this->buildPlayer($leader,$follower);
            //var_dump($player->describe());die('@306 in EntriesAssessGenerator');
            $choices = $player->getEventChoices();
            /** @var array $allKeys */
            if($selection['chosen']=='all'){
                //TODO: Choose all
            } else if ($selection['chosen']=='one') {
                $key = array_pop($allKeys);
                $arr = [$key];
                $player->updateInDb(['id'=>$player->getId(),
                                     'selections'=>$arr]);
            }
            array_push($participation['players'],$player);

        }
        array_push($this->participation, $participation);
    }

    /**
     * @param $data
     * @param $position
     * @return null|object
     * @throws GeneralException
     */
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
     * @throws ORMException
     * @throws OptimisticLockException
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
        $workarea = $this->workareaRepository->fetch($this->channel,$tag);
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
     * @param Workarea $workarea
     * @param int $modelId
     * @param array $data
     * @param array $position
     * @return Participant
     * @throws GeneralException
     */
    private function buildLeader(Workarea $workarea, int $modelId, array $data,array $position):Participant
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
        $genre = $data['genre'];

        /** @var Value $typeA */
        $typeA = $proficiency=='Professional'?
            $this->domainValueHash['type']['Professional']:
            $this->domainValueHash['type']['Amateur'];

        $genreValue = isset($this->domainValueHash['style'][$genre])?
            $this->domainValueHash['style'][$genre]:
            $this->domainValueHash['substyle'][$genre];
        $model=$this->modelRepository->findOneBy(['id'=>$modelId]);

        $leader = new Participant();
        $leader->setFirst($genre.'-'.$proficiency)
                ->setLast($age.'-'.$type.'-'.'M')
                ->setSex('M')
                ->setTypeA($typeA)
                ->setTypeB($this->domainValueHash['type'][$type])
                ->addModel($model)
                ->addGenreProficiency($genreValue,$this->domainValueHash['proficiency'][$proficiency]);

        switch($typeA->getName()){
            case 'Amateur':
                $leader->setYears(self::YEARS[$age]);
        }

        $this->participantRepository->save($workarea,$leader);
        $this->participantCount++;
        return $leader;
    }


    /**
     * @param Workarea $workarea
     * @param int $modelId
     * @param array $data
     * @param array $position
     * @return array
     * @throws GeneralException
     */
    private function buildFollowers(Workarea $workarea,
                                    int $modelId,
                                    array $data,
                                    array $position):array
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
        $followersAndSelections = $this->buildFollowersIteration(
                                            $workarea,
                                            $modelId,
                                            $data['genre'],
                                            $proficiencies,
                                            $proficienciesPosition,
                                            $ages,
                                            $agesPosition,
                                            $data['events'],
                                            $position['events']);
        return $followersAndSelections;
    }

    /**
     * @param Workarea $workarea
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
     */
    private function buildFollowersIteration(Workarea $workarea,
                                             int $modelId,
                                             string $genre,
                                             array $proficiencies,
                                             array $proficienciesPosition,
                                             array $ages,
                                             array $agesPosition,
                                             array $events,
                                             array $eventsPosition):array
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
                $follower = $this->buildSingleFollower($workarea, $modelId,$genre,$proficiency,$age);
                $selections = $this->eventSelection($events,$eventsPosition);
                $followers[]=['follower'=>$follower,'selections'=>$selections];
                list($age,$agePosition,,) = $this->next($ages,$agesPosition);
            }
            list($proficiency,$proficiencyPosition,,)
                = $this->next($proficiencies,$proficienciesPosition);
        }

        return $followers;
    }

    /**
     * @param $data
     * @param $position
     * @return mixed
     * @throws GeneralException
     */
    private function eventSelection($data, $position)
    {
        list($style,$stylePosition,$key,$keyPosition)=
            $this->current($data,$position);
        if($key!='style') {
            throw new GeneralException($key,$keyPosition,'expected "style"',
                        EntriesAssessExceptionCode::STYLE);
        }
        //TODO: Delete
        //var_dump(array_keys($this->domainValueHash['style']),$style);
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
            throw new GeneralException($key,$keyPosition,'expected "tag"', EntriesAssessExceptionCode::TAG);
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
     * @param Workarea $workarea
     * @param int $modelId
     * @param string $genre
     * @param string $proficiency
     * @param string $age
     * @return Participant
     */
    private function buildSingleFollower(Workarea $workarea, int $modelId,string $genre,string $proficiency,string $age)
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
        /** @var Model $model */
        $model = $this->modelRepository->find($modelId);
        /** @var Participant $follower */
        $follower = new Participant();
        $follower->setFirst($proficiency)
                ->setLast($age)
                ->setSex('F')
                ->setYears(self::YEARS[$age])
                ->setTypeA($typeAValue)
                ->setTypeB($typeBValue)
                ->addModel($model)
                ->addGenreProficiency($genreValue,$proficiencyValue);
        $this->participantRepository->save($workarea,$follower);
        $this->participantCount++;
        return $follower;
    }

    /**
     * @param Participant $p1
     * @param Participant $p2
     * @return \App\Entity\Sales\Iface\Player
     * @throws \App\Exceptions\ClassifyException
     */

    private function buildPlayer(Participant $p1, Participant $p2)
    {
        $player=$this->playerRepository->fetchPlayer($p1,$p2);

        $this->playerCount++;
        return $player;
     }
}