<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 7/16/18
 * Time: 11:08 PM
 */

namespace App\Entity\Sales\Iface;


class Summary
{
    private $id;

    private $currency;

    /**
     * @var array
     * $this->idCoupling[$participantId1][$modelId][$eventId]=$participantId2
     */
    private $idCoupling = [];

    /**
     * @var array
     * $this->idParticipantPlayers[$participantId1]=[$playerIdList]
     */
    private $idParticipantPlayers = [];

    /**
     * @var array
     * $this->idPlayerEvents[$playerId][$modelId]=[$eventIdList]
     */
    private $idPlayerEvents = [];

    /**
     * @var array
     * $this->idEvent[$modelId][$eventId]=$description
     */
    private $idEventDescription = [];

    /**
     * @var array
     * $this->participant[$participantId]=$participant
     */
    private $participant = [];

    /**
     * @var array
     */
    private $assessment = [];

    public function __construct(string $currency)
    {
        $this->currency = $currency;
    }

    public function setId(int $id) {
        $this->id=$id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    private function addPartnering(int $participantId, int $modelId, $eventIds, int $partnerId)
    {
        if(!isset($this->idCoupling[$participantId])) {
            $this->idCoupling[$participantId]=[];
        }
        if(!isset($this->idCoupling[$participantId][$modelId])) {
            $this->idCoupling[$participantId][$modelId]=[];
        }
        foreach($eventIds as $eventId) {
            if(!isset($this->idCoupling[$participantId][$modelId][$eventId])) {
                $this->idCoupling[$participantId][$modelId][$eventId]=$partnerId;
            }
        }
    }


    private function addCoupling( array $participantIds,
                                  int $modelId,
                                  array $eventIds)
    {
        list($p0,$p1) = isset($participantIds[1])?$participantIds:[$participantIds[0],null];
        $this->addPartnering($p0,$modelId,$eventIds,$p1);
        if(!is_null($p1)) {
            $this->addPartnering($p1,$modelId,$eventIds,$p0);
        }
    }

    private function addPlayerEventIds(int $playerId, int $modelId, array $eventSelections)
    {
        if(!isset($this->idPlayerEvents[$playerId][$modelId])) {
            $this->idPlayerEvents[$playerId][$modelId]=$eventSelections;
        }
    }



    private function addParticipantPlayerIds(int $participantId, int $playerId)
    {
        if(!isset($this->idParticipantPlayers[$participantId])) {
            $this->idParticipantPlayers[$participantId]=[];
        }
        array_push($this->idParticipantPlayers[$participantId],$playerId);
    }


    public function addModelEventDescription(int $modelId, int $eventId, array $description)
    {
        if(!isset($this->idEventDescription[$modelId])) {
            $this->idEventDescription[$modelId]=[];
        }
        if(!isset($this->idEventDescription[$modelId][$eventId])) {
            $this->idEventDescription[$modelId][$eventId]=$description;
        }
    }

    public function add(Player &$player)
    {
        foreach($player->getParticipantIds() as $participantId){
            $this->participant[$participantId]=$player->getParticipant($participantId);
        }
        $modelEventSelections = $player->getSelections();
        $playerId = $player->getId();
        $modelIdEventDescriptions = $player->getEvents();
        foreach($modelEventSelections as $modelId => $eventSelections)
        {
            $this->addCoupling($player->getParticipantIds(),$modelId, $eventSelections);
        }
        foreach($player->getParticipantIds() as $participantId) {
            $this->addParticipantPlayerIds($participantId, $playerId);
        }
        foreach ($modelEventSelections as $modelId => $eventSelectionIds) {
            $this->addPlayerEventIds($playerId,$modelId,$eventSelectionIds);
            $eventDescriptions = $modelIdEventDescriptions[$modelId];
            foreach($eventSelectionIds as $eventId){
                $description = $eventDescriptions[$eventId];
                $this->addModelEventDescription($modelId,$eventId,$description);
            }
        }
        return $this;
    }



    public function removePlayer(int $playerId)
    {

    }

    public function removeParticipant(int $participantId){

    }

    public function hasConflict(array $conflict)
    {
        /**
         * @var  int $modelId
         * @var  array $events
         */
        $eventCount = 0;
        foreach($conflict as $modelId=>$events)
        {
            $eventCount+=count($events);
        }
        return $eventCount?true:false;
    }

    private function locateConflict(int $nParticipant,
                                    int $modelId,
                                    array $events,
                                    int $nPartner,
                                    array &$conflict)
    {
        $temp=[];
        if(!isset($this->idCoupling[$nParticipant])) return;
        if (isset( $this->idCoupling[$nParticipant][$modelId] )) {
            foreach (array_keys( $events ) as $eventId) {
                if (isset( $this->idCoupling[$nParticipant][$modelId][$eventId] )) {
                    $nPriorPartner = $this->idCoupling[$nParticipant][$modelId][$eventId];
                    if ($nPartner != $nPriorPartner) {
                        array_push( $temp, $eventId );
                    }
                }
            }
        }

        foreach($temp as $eventId) {
            array_push($conflict[$modelId],$eventId);
        }
    }


    public function eventConflicts(Player &$player): array
    {
        $participantIds = $player->getParticipantIds();
        list($p0,$p1) = isset($participantIds[1])?$participantIds:[$participantIds[0],null];
        // If first partnership then no potential conflicts.
        if(!isset($this->idCoupling[$p0])) {
            return [];
        }

        // If solo performance then $p1 is null and no potential conflicts
        if(is_null($p1)) {
            return [];
        }

        $modelEvents = $player->getEvents();

        $conflict = [];
        foreach($modelEvents as $modelId=>$events) {
            if (!isset( $conflict[$modelId])) {
                $conflict[$modelId] = [];
            }
            $this->locateConflict($p0,$modelId,$events,$p1,$conflict);
            $this->locateConflict($p1,$modelId,$events,$p0,$conflict);
        }
        return $this->hasConflict($conflict)?$conflict:[];
    }

    public function setXtras(Xtras $xtras)
    {

    }


    public function preJSON():array
    {

    }

    public function toArray()
    {
        return
            ['idCoupling' => $this->idCoupling,
             'idParticipantPlayers' => $this->idParticipantPlayers,
             'idPlayerEvents'=> $this->idPlayerEvents,
             'idEventDescription'=> $this->idEventDescription];
    }

    public function init($data):array
    {
        $this->idCoupling = $data['idCoupling'];
        $this->idParticipantPlayers = $data['idParticipantPlayers'];
        $this->idPlayerEvents = $data['idPlayerEvents'];
        $this->idEventDescription = $data['idEventDescription'];
        return array_keys($this->idParticipantPlayers);
    }

    public function initParticipant(Participant $participant)
    {
        $this->participant[$participant->getId()]=$participant;
    }

    public function describe():array
    {
        $data = [];
        foreach($this->idCoupling as $p0=>$modelEventPartnerList){
            $lead=$this->participant[$p0]->getName();
            $data[$lead]=[];
            foreach($modelEventPartnerList as $modelId=>$eventPartnerList) {
                $data[$lead][$modelId]=[];
                foreach($eventPartnerList as $eventId=>$p1) {
                    $follow = $this->participant[$p1]->getName();
                    $event = $this->idEventDescription[$modelId][$eventId];
                    $description = ['event'=>$event];
                    if($follow){
                        $description['with']=$follow;
                    }
                    $data[$lead][$modelId][$eventId]=$description ;
                }
            }
        }
        return $data;
    }
}