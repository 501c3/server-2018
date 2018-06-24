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
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class PlayerRepository
{
    /**
     * @var ValueRepository
     */
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
     * PlayerRepository constructor.
     * @param ValueRepository $valueRepository
     * @param ModelRepository $modelRepository
     * @param TagRepository $tagRepository
     * @param FormRepository $formRepository
     * @param CompetitionRepository $competitionRepository
     * @param IfaceRepository $ifaceRepository
     * @param CompetitionPlayerRepository $playerRepository
     * @param EventRepository $eventRepository
     */

    public function __construct(
        ValueRepository $valueRepository,
        ModelRepository $modelRepository,
        TagRepository $tagRepository,
        FormRepository $formRepository,
        CompetitionRepository $competitionRepository,
        IfaceRepository $ifaceRepository,
        CompetitionPlayerRepository $playerRepository,
        EventRepository $eventRepository)
    {

        $this->valueRepository = $valueRepository;
        $this->modelRepository = $modelRepository;
        $this->tagRepository = $tagRepository;
        $this->formRepository = $formRepository;
        $this->playerRepository = $playerRepository;
        $this->eventRepository = $eventRepository;
        $this->ifaceRepository = $ifaceRepository;
        $this->competitionRepository = $competitionRepository;
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
            ->setProficiencyMapping($mapping['proficiency'])
            ->setAgeMapping($mapping['age']);

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

    public function create(Workarea $workarea, int $p1, int $p2=null)
    {
        //TODO: Reconstruct Participant Objects
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
                $genre = $this->valueById[$genreId];
                $qualification=$player->getQualificationByKeys($modelId,$genreId);
                $q = $qualification->toArray(Qualification::DOMAIN_NAME_TO_VALUE_ID);
                $playerId=$playerLookup[strval($q['genre'])]
                                       [strval($q['proficiency'])]
                                       [strval($q['age'])]
                                       [strval($q['type'])];
                /** @var CompetitionPlayer $competitionPlayer */
                $competitionPlayer=$this->playerRepository->find($playerId);
                /** @var Event $events */
                $events=$this->eventRepository->fetchPlayerEvents($model,$competitionPlayer);
                foreach($events as $event) {
                    $data=$event->getValue();
                    $data['id']=$event->getId();
                    $data['model']=$modelId;
                    $data['etag']=$event->getTag();
                    $player->addEvents($model,$data);
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
                    ->setTypeA($typeA)
                    ->setTypeB($typeB);
        foreach($data['genreProficiency'] as $genreId=>$proficiencyId) {
            $genreValue = $this->valueById[$genreId];
            $proficiencyValue = $this->valueById[$proficiencyId];
            $participant->addGenreProficiency($genreValue,$proficiencyValue);
        }
        return $participant;
    }

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

        if (isset( $data['assessment'] )) {
            $date = new \DateTime( $data['assessment-date'] );
            $player->setAssessment( $date, $data['assessment'] );
        }

        return $player;
    }
}