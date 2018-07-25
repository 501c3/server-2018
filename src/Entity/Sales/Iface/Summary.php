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
     * $this->idPlayerParticipant[$playerId]=[$participantIdList]
     */
    private $idPlayerParticipants = [];

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
     * $this->participant[$participantId]=(Participant) $participant
     */
    private $participant = [];

    /**
     * @var array
     * $this->inventory[$inventoryId]=['tag'=>string, 'name'=>string,'unitPrice'=>float]
     *
     */
    private $inventory;

    /**
     * @var array
     * $this->assessment['comp'][$playerId]=['dances'=>#, 'charge'=>$];
     * $this->assessment['exam'][$playerId]=['dances'=>#, 'charge'=>$]
     */
    private $assessment = ['comp'=>[],'exam'=>[]];

    /**
     * @var array
     * $this->xtras[$inventoryId]=['qty'=>#, 'description'=> string, 'unitPrice'=>float]
     */
    private $xtras = [];

    /**
     * @var array
     * $this->payment[$date]
     */
    private $payments = [];

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

    private function summaryParticipantFields(Participant $participant)
    {
        return ['first'=>$participant->getFirst(),
                'last'=>$participant->getLast(),
                'sex'=>$participant->getSex(),
                'years'=>$participant->getYears(),
                'typeA'=>$participant->getTypeA()->getName(),
                'typeB'=>$participant->getTypeB()->getName()];
    }

    public function add(Player &$player)
    {
        foreach($player->getParticipantIds() as $participantId){
            $this->participant[$participantId]
                =$this->summaryParticipantFields(
                        $player->getParticipant($participantId));
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
        $this->idPlayerParticipants[$player->getId()]=$player->getParticipantIds();
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

        $participantIds = [];
        foreach($this->idParticipantPlayers as $idParticipant=>$idPlayerList) {
            if(in_array($playerId,$idPlayerList)) {
                $participantIds[]=$idParticipant;
                $this->idParticipantPlayers[$idParticipant]=array_diff($idPlayerList,[$playerId]);
            }
        }
        unset($this->idPlayerParticipants[$playerId]);

        switch(count($participantIds)){
            case 1:
                $p0=$participantIds[0];
                foreach($this->idCoupling as $pl=>$idModelEvent){
                    foreach($idModelEvent as $modelId=>$events) {
                        foreach($events as $eventId){
                            if(is_null($this->idCoupling[$p0][$modelId][$eventId])){
                                unset($this->idCoupling[$p0][$modelId][$eventId]);
                            }
                        }
                    }
                }
                break;
            case 2:
                $p0=$participantIds[0];
                $p1=$participantIds[1];
                foreach($this->idCoupling as $pl=>$idModelEvent) {
                    if($p0==$pl) {
                        foreach ($idModelEvent as $modelId => $events) {
                            foreach($events as $eventId=>$pr){
                                if($p1==$pr) {
                                    unset($this->idCoupling[$p0][$modelId][$eventId]);
                                }
                            }
                        }
                    }
                }
                foreach($this->idCoupling as $pl=>$idModelEvent) {
                    if($p1==$pl){
                        foreach ($idModelEvent as $modelId => $events) {
                            foreach($events as $eventId=>$pr){
                                if($p0==$pr) {
                                    unset($this->idCoupling[$p1][$modelId][$eventId]);
                                }
                            }
                        }
                    }
                }
        }
        foreach($this->idCoupling as $pl=>$idModelEvent) {
            foreach(array_keys($idModelEvent) as $modelId) {
                if(!count($this->idCoupling[$pl][$modelId])){
                    unset($this->idCoupling[$pl][$modelId]);
                }
            }
            if(!count($this->idCoupling[$pl])) {
                unset($this->idCoupling[$pl]);
            }
        }
        unset($this->idPlayerEvents[$playerId]);
    }

    public function removeParticipant(int $participantId):array
    {
        $playerIds  = $this->idParticipantPlayers[$participantId];
        foreach($playerIds as $playerId) {
            unset($this->idPlayerEvents[$playerId]);
        }
        unset($this->idParticipantPlayers[$participantId]);
        foreach($this->idCoupling as $pl=>$idModelEvents) {
          if($pl==$participantId){
            unset($this->idCoupling[$pl]);
            continue;
          }
          foreach($idModelEvents as $modelId=>$events){
              foreach($events as $eventId=>$pr){
                  if($pr == $participantId){
                      unset($this->idCoupling[$pl][$modelId][$eventId]);
                  }
              }
              if(!count($this->idCoupling[$pl][$modelId])){
                  unset($this->idCoupling[$pl][$modelId]);
              }
          }
        }
        foreach($playerIds as $playerId){
            unset($this->idPlayerParticipants[$playerId]);
        }
        return $playerIds;
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

    public function getParticipants():array
    {
        return $this->participant;
    }


    public function setXtras(Xtras $xtras): Summary
    {
        $this->xtras = $xtras->toArray();
        return $this;
    }

    public function setInventory(array $data): Summary
    {
        $this->inventory=$data;
        return $this;
    }


    public function toArray()
    {
        return
            ['idCoupling' => $this->idCoupling,
             'idParticipantPlayers' => $this->idParticipantPlayers,
             'idPlayerEvents'=> $this->idPlayerEvents,
             'idEventDescription'=> $this->idEventDescription,
             'participant'=>$this->participant,
             'assessment'=>$this->assessment,
             'xtras'=>$this->xtras,
             'payments'=>$this->payments
            ];
    }

    public function init($data)
    {
        $this->idCoupling = $data['idCoupling'];
        $this->idParticipantPlayers = $data['idParticipantPlayers'];
        $this->idPlayerParticipants = $data['idPlayerParticipants'];
        $this->idPlayerEvents = $data['idPlayerEvents'];
        $this->idEventDescription = $data['idEventDescription'];
        $this->participant = $data['participant'];
        $this->xtras = $data['xtras'];
    }


    public function describe():array
    {
        $data = [];
        $data['inventory']=$this->inventory;
        foreach($this->idCoupling as $p0=>$modelEventPartnerList){
            $lead=$this->participant[$p0]['first'].' '.$this->participant[$p0]['last'];
            $data[$lead]=[];
            foreach($modelEventPartnerList as $modelId=>$eventPartnerList) {
                $data[$lead][$modelId]=[];
                foreach($eventPartnerList as $eventId=>$p1) {
                    $follow = $this->participant[$p1]['first'].' '.$this->participant[$p1]['last'];
                    $event = $this->idEventDescription[$modelId][$eventId];
                    $description = ['event'=>$event];
                    if($follow){
                        $description['with']=$follow;
                    }
                    $data[$lead][$modelId][$eventId]=$description ;
                }
            }
        }
        $data['xtras']=$this->xtras;
        $data['payments']=$this->payments;
        return $data;
    }

    private function orderInfo(array $info0, array $info1):array
    {
        $compare1=strcmp($info0['sex'],$info1['sex']);
        if($compare1<0){
            return [$info0,$info1];
        }
        if($compare1>0){
            return [$info1,$info0];
        }
        $compare2=strcmp($info0['last'],$info1['last']);
        if($compare2<0){
            return [$info0,$info1];
        }
        if($compare2>0){
            return [$info1,$info0];
        }
        $compare3=strcmp($info0['first'],$info1['first']);
        if($compare3<0){
            return [$info0,$info1];
        }
        if($compare3>0){
            return [$info1,$info0];
        }
        return [$info0,$info1];
    }

    private function getPlayerDataOrdered(int $playerId)
    {
        $participantIds = $this->idPlayerParticipants[$playerId];
        switch(count($participantIds)){
            case 1:
                $p0=$participantIds[0];
                $info=$this->participant[$p0];
                return [$info];
            case 2:
                $info0=$this->participant[$participantIds[0]];
                $info1=$this->participant[$participantIds[1]];
                return $this->orderInfo($info0,$info1);
        }
    }

    private function mergeModelEventCollections(array $modelEventCollections)
    {
        $merged = [];
        foreach($modelEventCollections as $modelId=>$eventCollection){
            $merged[$modelId]=array_merge(...$eventCollection);
        }
       return $merged;
    }


    public function preJSON():array
    {
        $participation = [];
        $modelEventCollections = [];
        foreach($this->idPlayerEvents as $playerId=>$modelEventList) {
            $participation[$playerId]=['participants'=>$this->getPlayerDataOrdered($playerId),
                                       'idModelEvents'=>[]];
            foreach($modelEventList as $modelId=>$eventList){
                if(!isset($modelEventCollections[$modelId])){
                    $modelEventCollections[$modelId]=[];
                }
                array_push($modelEventCollections[$modelId],$eventList);
                $participation[$playerId]['idModelEvents'][$modelId]=$eventList;
            }
        }
        $modelEvents = $this->mergeModelEventCollections($modelEventCollections);
        $descriptions = [];
        foreach($modelEvents as $modelId=>$eventIds){
            if(!isset($descriptions[$modelId])){
                $descriptions[$modelId]=[];
            }
            foreach($eventIds as $eventId) {
                $descriptions[$modelId][$eventId]=$this->idEventDescription[$modelId][$eventId];
            }
        }

        $preJSON=['participation'=>$participation,
                  'eventDescription'=>$descriptions];

        return $preJSON;
    }


    private function inventoryCharges()
    {
       $charges=[];
       foreach($this->inventory as $inventoryId=>$record) {
           $charges[$record['description']]=$record['unitPrice'];
       }
       return $charges;
    }


    public function assess()
    {
        $charges = $this->inventoryCharges();
        $this->assessment=['comp'=>[],'exam'=>[]];
        foreach ($this->idPlayerEvents as $playerId => $modelEvents) {
            foreach ($modelEvents as $modelId => $eventList) {
                foreach ($eventList as $eventId) {
                    $description = $this->idEventDescription[$modelId][$eventId];
                    $cnt = count($description['dances']);
                    if (in_array( $description['age'],
                        ['Baby', 'Juvenile', 'Preteen 1', 'Preteen 2', 'Junior 1', 'Junior 2', 'Youth'] )) {
                        if(!isset($this->assessment['comp'][$playerId])) {
                            $this->assessment['comp'][$playerId]=['dances'=>0,'charge'=>0.0];
                        }
                        $this->assessment['comp'][$playerId]['dances']+=$cnt;
                        $this->assessment['comp'][$playerId]['charge']+=$cnt*$charges['Per Dance Child'];
                    } else if (in_array( $description['age'],
                        ['Adult', 'Senior 1', 'Senior 2', 'Senior 3', 'Senior 4', 'Senior 5'] )) {
                        if(!isset($this->assessment['comp'][$playerId])) {
                            $this->assessment['comp'][$playerId]=['dances'=>0,'charge'=>0.0];
                        }
                        $this->assessment['comp'][$playerId]['dances']+=$cnt;
                        $this->assessment['comp'][$playerId]['charge']+=$cnt*$charges['Per Dance Adult'];
                    } else if (in_array( $description['age'],
                        ['Under 6', 'Under 8', 'Under 12', 'Junior 12-16'] )) {
                        if(!isset($this->assessment['exam'][$playerId])) {
                                $this->assessment['exam'][$playerId]=['dances'=>0,'charge'=>0.0];
                        }
                        $this->assessment['exam'][$playerId]['dances']+=$cnt;
                        $this->assessment['exam'][$playerId]['charge']+=$cnt*$charges['Exam Per Dance Child'];

                    }else if (in_array( $description['age'],
                        ['Adult 16-50', 'Senior 50'] )) {
                        if(!isset($this->assessment['exam'])) {
                            $this->assessment['exam'][$playerId]=['dances'=>0,'charge'=>0.0];
                        }
                        $this->assessment['exam'][$playerId]['dances']+=$cnt;
                        $this->assessment['exam'][$playerId]['charge']+=$cnt*$charges['Exam Per Dance Adult'];
                    }
                }
            }
        }
        return $this->assessment;
    }

    public function assessTotal()
    {

    }
}