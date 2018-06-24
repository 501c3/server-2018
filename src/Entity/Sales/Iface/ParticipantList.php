<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/13/18
 * Time: 8:35 PM
 */

namespace App\Entity\Sales\Iface;


use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\PricingRepository;

class ParticipantList
{
    /**
     * @var FormRepository
     */
    private $formRepository;
    /**
     * @var Tag
     */
    private $participantTag;
    /**
     * @var Workarea
     */
    private $workarea;
    /**
     * @var array
     */
    private $domainValueHash;
    /**
     * @var array
     */
    private $valueById;
    /**
     * @var Tag
     */
    private $playerTag;
    /**
     * @var array
     */
    private $modelById;

    private $participantList;


    public function __construct(array $valueById,
                                array $modelById,
                                Workarea $workarea,
                                FormRepository $formRepository,
                                Tag $participantTag,
                                Tag $playerTag)
    {
        $this->formRepository = $formRepository;
        $this->participantTag = $participantTag;
        $this->workarea = $workarea;
        $this->domainValueHash = $domainValueHash;
        $this->valueById = $valueById;
        $this->playerTag = $playerTag;
        $this->modelById = $modelById;
    }

    public function fetch() {
        $forms=$this->formRepository->fetchList($this->workarea,$this->participantTag);
        $unsorted = [];
        $keyValue = [];
        /** @var Form $form */
        foreach($forms as $form) {
            $content=$form->getContent();
            $first = $content['first'];
            $last = $content['last'];
            $id = $form->getId();
            $unsorted[$id]=['first'=>$first,'last'=>$last];
            $keyValue[$id]=[$last.', '.$first];
        }
        $result=asort($keyValue);
        return $result;
    }

    public function add(array $data)
    {
        $participant = new Participant($this->valueById,$this->modelById,$this->formRepository,$this->participantTag);
        $participant->createInDb($this->workarea,$data);
        $name = $participant->getName();
        $id = $participant->getId();
        $this->participantList[$id]=$name;
        return $id;
    }

    public function delete(int $participantId)
    {
        $forms = $this->formRepository->fetchList($this->workarea,$this->playerTag);
        $em = $this->formRepository->getEntityManager();
        foreach($forms as $form) {
            $data=$form->getContent();
            if(in_array($participantId, $data['participants'])) {
                $em->remove($form);
                $em->flush();
            }
        }

    }

}