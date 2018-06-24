<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/18/18
 * Time: 5:38 PM
 */

namespace App\Entity\Sales\Iface;


use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\FormRepository;

class PlayerList
{
    /**
     * @var FormRepository
     */
    private $formRepository;
    /**
     * @var Tag
     */
    private $playerTag;
    /**
     * @var Workarea
     */
    private $workarea;


    private $playerIdName = [];



    public function __construct(
        FormRepository $formRepository,
        Workarea $workarea,
        Tag $playerTag
    )
    {
        $this->formRepository = $formRepository;
        $this->playerTag = $playerTag;
        $this->workarea = $workarea;
    }


    /**
     * @param Player $player
     * @return $this
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function add(Player $player)
    {
        $selections=$player->eventSelections();
        $playerParticipantIds = $player->getParticipantIds();
        $forms=$this->formRepository->fetchList($this->workarea,$this->playerTag);
        /** @var Form $form */
        foreach($forms as $form){
            if($form->getId()==$player->getId()) continue;
            $data=$form->getContent();
            $exclusions = isset($data['exclusions'])?$data['exclusions']:[];
            $priorExclusions = array_keys($exclusions);
            $otherParticipantIds = $data['participants'];
            $allParticipants = array_unique(array_merge($playerParticipantIds,$otherParticipantIds));
            $remainingParticipant = array_diff($allParticipants, $otherParticipantIds);
            if(!count($remainingParticipant)) continue;
            foreach($data['events'] as $genreEvents){
                foreach($genreEvents as $events){
                    $choices = array_keys($events);
                    $intersection=array_intersect($choices,$selections);
                    if(count($intersection)) {
                       foreach($intersection as $exclude){
                            if(in_array($exclude,array_keys($priorExclusions))) continue;
                            $exclusion[$exclude] = $remainingParticipant[0];
                       }
                    }
                }
            }
            $data['exclusions'] = $exclusions;
            $form->setContent($data);
            $em=$this->formRepository->getEntityManager();
            $em->persist($form);
            $em->flush();
            $this->pushToList($data,$player->getId());
        }
        return $this;
    }

    /**
     * @param Player $player
     * @return $this
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Player $player)
    {
        $participantIds = $player->getParticipantIds();
        $forms=$this->formRepository->fetchList($this->workarea,$this->playerTag);
        /** @var Form $form */
        foreach($forms as $form){
            if($form->getId()==$player->getId()) continue;
            $data=$form->getContent();
            $exclusions = isset($data['exclusions'])?$data['exclusions']:[];
            foreach($exclusions as $eventId=>$participantId) {
               if(in_array($participantId, $participantIds)) {
                   unset($exclusions[$eventId]);
               }
            }
            $data['exclusions'] = $exclusions;

            $form->setContent($data);
            $em=$this->formRepository->getEntityManager();
            $em->persist($form);
            $em->flush();
            $this->pushToList($data, $player->getId());
        }
        return $this;
    }

    private function pushToList($data, $playerId) {
        $participantIds = $data['participants'];

        $names  = [];
        foreach($participantIds as $id) {
            $form=$this->formRepository->fetchForm($id);
            $data = $form->getContent();
            $last = $data['last'];
            $first = $data['first'];
            array_push($names, "$last, $first");
        }
        switch(count($names)) {
            case 1:
                $this->playerIdName[$playerId] = $names[0];
                break;
            case 2:
                $this->playerIdName[$playerId] = $names[0].' & '.$names[1];
        }
    }

    public function get()
    {
        $list=$this->namesPlayerId;
        asort($list);
        return $list;
    }


}