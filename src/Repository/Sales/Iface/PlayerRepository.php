<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/19/18
 * Time: 10:38 PM
 */

namespace App\Repository\Sales\Iface;

use App\Entity\Competition\Competition;
use App\Entity\Competition\Event;
use App\Entity\Competition\Iface;
use App\Entity\Competition\Model;
use App\Entity\Competition\Player as CompetitionPlayer;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Form;
use App\Entity\Sales\Iface\Classify;
use App\Entity\Sales\Iface\GeorgiaDanceSportClassify;
use App\Entity\Sales\Iface\Participant;
use App\Entity\Sales\Iface\Player;
use App\Entity\Sales\Iface\PlayerList;
use App\Entity\Sales\Iface\Qualification;
use App\Entity\Sales\Workarea;
use App\Exceptions\ClassifyException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\EventRepository;
use App\Repository\Competition\IfaceRepository;
use App\Repository\Competition\PlayerRepository as CompetitionPlayerRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class PlayerRepository
{
    /** @var ValueRepository*/

    private $valueRepository;
    /**
     * @var ModelRepository
     */
    private $modelRepository;
    /**
     * @var TagRepository
     */
    private $tagRepository;

    /** @var FormRepository */
    private $formRepository;

    /** @var CompetitionPlayerRepository*/
    private $playerRepository;

    /** @var EventRepository*/
    private $eventRepository;

    /** @var Classify */
    private $classify;

    /** @var IfaceRepository*/
    private $ifaceRepository;

    /** @var CompetitionRepository*/
    private $competitionRepository;

    private $modelPlayerLookup;

    /** @var array */
    private $domainValueHash ;

    /** @var array */
    private $valueById;

    /** @var array */
    private $modelById;

    private $debug;
    /**
     * @var SummaryRepository
     */
    private $summaryRepository;

    /**
     * PlayerRepository constructor.
     * @param ValueRepository $valueRepository
     * @param ModelRepository $modelRepository
     * @param TagRepository $tagRepository
     * @param FormRepository $formRepository
     * @param CompetitionRepository $competitionRepository
     * @param IfaceRepository $ifaceRepository
     * @param CompetitionPlayerRepository $playerRepository
     * @param EventRepository $eventRepository
     * @param SummaryRepository $summaryRepository
     */

    public function __construct(
        ValueRepository $valueRepository,
        ModelRepository $modelRepository,
        TagRepository $tagRepository,
        FormRepository $formRepository,
        CompetitionRepository $competitionRepository,
        IfaceRepository $ifaceRepository,
        CompetitionPlayerRepository $playerRepository,
        EventRepository $eventRepository,
        SummaryRepository $summaryRepository)
    {
        $this->valueRepository = $valueRepository;
        $this->modelRepository = $modelRepository;
        $this->tagRepository = $tagRepository;
        $this->formRepository = $formRepository;
        $this->playerRepository = $playerRepository;
        $this->eventRepository = $eventRepository;
        $this->ifaceRepository = $ifaceRepository;
        $this->competitionRepository = $competitionRepository;
        $this->summaryRepository = $summaryRepository;
    }

    /**
     * @param Competition $competition
     * @param Classify $classify
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function configureClassifier(Competition $competition, Classify &$classify):void
    {
        if(isset($this->classify)) {
            return;
        }
        /** @var Iface $iface */
        $iface = $this->ifaceRepository->findOneBy(['competition'=>$competition]);
        $mapping = $iface->getMapping();
        $playerTag = !isset($this->playerTag)?$this->tagRepository->fetch('player'):$this->playerTag;
        $participantTag = !isset($this->participantTag)?$this->tagRepository->fetch('participant'):$this->participantTag;
        $domainValueHash = $this->valueRepository->fetchDomainValueHash();
        $valueById = $this->valueRepository->fetchAllValuesById();
        $modelsById = $this->modelRepository->fetchModelById();
        $classify->setDomainValueHash($domainValueHash)
                    ->setValueById($valueById)
                    ->setModelById($modelsById)
                    ->setParticipantTag($participantTag)
                    ->setPlayerTag($playerTag)
                    ->setProficiencyMapping($mapping['proficiency']);


        $this->modelPlayerLookup = [];
        /**
         * @var int $modelId
         * @var Model $model
         */
        $this->modelPlayerLookup = [];
        foreach($modelsById as $modelId=>$model) {
            $this->modelPlayerLookup[$modelId]=$model->getPlayerlookup();
        }
        $this->domainValueHash=$this->valueRepository->fetchDomainValueHash();
        $this->classify = $classify;
        $this->valueById=$valueById;
        $this->modelById=$modelsById;
    }

    /**
     * @param Channel $channel
     * @param bool $debug
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function initClassifier(Channel $channel,bool $debug=false)
    {
        $this->debug=$debug;
        switch($channel->getName()) {
            case "Georgia DanceSport":
                /** @var Competition $competition */
                $competition = $this->competitionRepository->findOneBy(['name'=>'Georgia DanceSport Competition and Medal Exams']);
                /** @var Iface $iface */
                $classify = new GeorgiaDanceSportClassify();
                $this->configureClassifier($competition,$classify);
        }
    }

    /**
     * @param Workarea $workarea
     * @return PlayerList|null
     * @throws ORMException
     * @throws OptimisticLockException
     */

    public function fetchList(Workarea $workarea): ?PlayerList
    {
        $tag=$this->tagRepository->fetch('player');
        $forms=$this->formRepository->findBy(['tag'=>$tag, 'workarea'=>$workarea]);
        if(!count($forms)) {
            return null;
        }
        $list=new PlayerList();
        /** @var Form $form */
        foreach($forms as $form)
        {
            $id=$form->getId();
            $content = $form->getContent();
            $participants=$this->readParticipants($content['participants']);
            /** @var Participant $participant */
            $team = [];
            foreach($participants as $participant) {
                $team[$participant->getId()] =
                    [
                        'first' => $participant->getFirst(),
                        'last' => $participant->getLast(),
                        'typeA' => $participant->getTypeA()->getName(),
                        'typeB' => $participant->getTypeB()->getName(),
                        'sex'=>$participant->getSex()
                    ];
            }
            $list->addPlayer($id,$team);
        }
        return $list;
    }

    /**
     * @param int $id
     * @return PlayerRepository
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(int $id):PlayerRepository
    {
       $this->formRepository->deleteForm($id);
       return $this;
    }

    /**
     * @param array $playerIds
     * @return $this
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteList(array $playerIds)
    {
        $this->formRepository->deleteFormList($playerIds);
        return $this;
    }


    /**
     * @param Workarea $workarea
     * @param int $p1
     * @param int|null $p2
     * @return Player
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function create(Workarea $workarea, int $p1, int $p2=null):Player
    {
        $list=$p2?$this->readParticipants([$p1,$p2]):$this->readParticipants([$p1]);

        switch(count($list)){
            case 1:
                return $this->createAux($workarea, $list[0]);
            case 2:
                return $this->createAux($workarea, $list[0],$list[1]);
        }
        throw new \Exception("Could not create Player.  Participant keys invalid",9000);
    }


    /**
     * @param Workarea $workarea
     * @param Participant $p1
     * @param Participant|null $p2
     * @return Player
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAux(Workarea $workarea, Participant $p1,Participant $p2=null): Player
    {
        if(!isset($this->classify))
        {
            $name=$p1->getName();
            throw new ClassifyException("$name","Failed to configure classification" ,9000);
        }
        /** @var Player $player */
        $player = is_null($p2)?$this->classify->solo($p1):$this->classify->couple($p1,$p2);
        $modelGenreKeys = $player->getModelGenreKeys();

        foreach($modelGenreKeys as $modelId=>$genreKeys) {
            /** @var Model $model */
            $model=$this->modelRepository->findOneBy(['id'=>$modelId]);

            $playerLookup=$model->getPlayerlookup();
            foreach($genreKeys as $genreId) {
                $qualification=$player->getQualificationByKeys($modelId,$genreId);
                $q = $qualification->toArray(Qualification::DOMAIN_NAME_TO_VALUE_ID);
               // TODO: Delete next line when debugged.
               // $qAux =$qualification->toArray(Qualification::DOMAIN_NAME_TO_VALUE_NAME);
                $playerId=$playerLookup[strval($q['genre'])]
                                       [strval($q['proficiency'])]
                                       [strval($q['age'])]
                                       [strval($q['type'])];
                /** @var CompetitionPlayer $competitionPlayer */
                $competitionPlayer=$this->playerRepository->find($playerId);
                $events=$this->eventRepository->fetchPlayerEvents($model,$competitionPlayer);
                /** @var Event $event */
                foreach($events as $event) {
                    $data=$event->getValue();
                    $data['id']=$event->getId();
                    $data['model']=$modelId;
                    $data['etag']=$event->getTag();
                    switch($data['tag']){
                        case 'Couple':
                            if($p2){
                                $player->addEvents($model,$data);
                            }
                            break;
                        case 'Solo':
                            if(!$p2){
                                $player->addEvents($model,$data);
                            }
                            break;
                        case 'Grandparent Child':
                        case 'Parent Child':
                            if($p2){
                                $player->addEvents($model,$data);
                            }
                    }
                }
            }
        }
        $data=$player->toArray();
        if($this->debug) {
            $data['describe']=$player->describe();
        }
        $tag = $this->tagRepository->fetch('player');
        $form = new Form();
        $form->setTag($tag)
            ->setWorkarea($workarea)
            ->setContent($data)
            ->setUpdatedAt(new \DateTime('now'));
        $em = $this->formRepository->getEntityManager();
        $em->persist($form);
        $em->flush();
        $player->setId($form->getId());
        return $player;
    }


    private function buildParticipant($data):Participant
    {
        $typeA = $this->valueById[$data['typeA']];
        $typeB= $this->valueById[$data['typeB']];
        $participant = new Participant();
        $participant->setFirst($data['first'])
                    ->setLast($data['last'])
                    ->setYears($data['years'])
                    ->setSex($data['sex'])
                    ->setTypeA($typeA)
                    ->setTypeB($typeB);
        foreach($data['genreProficiency'] as $genreId=>$proficiencyId) {
            $genreValue = $this->valueById[$genreId];
            $proficiencyValue = $this->valueById[$proficiencyId];
            $participant->addGenreProficiency($genreValue,$proficiencyValue);
        }
        foreach($data['models'] as $modelId){
            /** @var Model $model */
            $model=$this->modelRepository->find($modelId);
            $participant->addModel($model);
        }
        return $participant;
    }

    /**
     * @param $participantIds
     * @return array
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    private function readParticipants($participantIds): array
    {
        $list = [];
        foreach($participantIds as $id){
            /** @var Form $form */
            $form=$this->formRepository->fetchForm($id);
            $data=$form->getContent();
            $participant = $this->buildParticipant($data);
            $participant->setId($id);
            $list[]=$participant;
        }
        return $list;
    }

    /**
     * @param int $id
     * @return Player
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function read(int $id):Player
    {
        $form = $this->formRepository->find( $id );
        $data = $form->getContent();
        $participants = $this->readParticipants( $data['participants'] );
        $player = new Player();
        foreach ($participants as $participant) {
            $player->addParticipant( $participant );
        }

        foreach ($data['events'] as $modelId => $eventList) {
            $model = $this->modelById[$modelId];
            foreach($eventList as $events){
                $player->addEvents( $model, $events );
            }
        }

        foreach ($data['qualifications'] as $modelId => $qualifiers) {
            $model = $this->modelById[$modelId];
            foreach ($qualifiers as $valueIdList) {
                $list = [];
                foreach ($valueIdList as $id) {
                    $list[] = $this->valueById[$id];
                }
                $qualifier = new Qualification();
                $qualifier->set( $list );
                $player->addQualification( $model, $qualifier );
            }
        }
        if (isset( $data['selections'] )) {
            $player->setSelections( $data['selections'] );
        }

        if (isset( $data['exclusions'] )) {
            $player->setExclusions( $data['exclusions'] );
        }
        return $player;
    }


    public function save(Player $player)
    {
        $id=$player->getId();
        $form=$this->formRepository->find($id);
        $form->setContent($player->toArray());
        /** @var EntityManagerInterface $em */
        $em=$this->formRepository->getEntityManager();
        $em->flush();
    }


}