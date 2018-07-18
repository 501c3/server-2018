<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/19/18
 * Time: 10:37 PM
 */

namespace App\Repository\Sales\Iface;


use App\Entity\Sales\Form;
use App\Entity\Sales\Iface\Participant;
use App\Entity\Sales\Iface\ParticipantList;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use App\Repository\Competition\ModelRepository;
//use App\Repository\Models\ValueRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\TagRepository;

class ParticipantRepository
{
    //TODO: Delete commented lines.
    /*
     * @var ValueRepository
     */
    //private $valueRepository;
    /**
     * @var ModelRepository
     */
    private $modelRepository;
    /**
     * @var FormRepository
     */
    private $formRepository;
    /**
     * @var TagRepository
     */
    private $tagRepository;

    public function __construct(
        ModelRepository $modelRepository,
        FormRepository $formRepository,
        TagRepository $tagRepository
    )
    {
        //$this->valueRepository = $valueRepository;
        $this->modelRepository = $modelRepository;
        $this->formRepository = $formRepository;
        $this->tagRepository = $tagRepository;
    }

    public function fetch(int $id)
    {

    }

    public function fetchList(Workarea $workarea): ?ParticipantList
    {
        $tag=$this->tagRepository->fetch('participant');
        $forms=$this->formRepository->findBy(['tag'=>$tag, 'workarea'=>$workarea]);
        if(!count($forms)) {
            return null;
        }
        $list=new ParticipantList(ParticipantList::DISPLAY_NAME_NORMAL);
        foreach($forms as $form) {
            $id=$form->getId();
            $content = $form->getContent();
            $content['id']=$id;
            $list->add($content);
        }
        return $list;
    }

    private function fetchForm(Workarea $workarea, Tag $tag, Participant &$participant)
    {
        if($participant->hasId()) {
            /** @var Form $form */
            $form = $this->formRepository->find($participant->getId());
            $data = $participant->toArray();
            $form->setContent($data)
                ->setUpdatedAt(new \DateTime('now'));
            return $form;
        }
        $form = new Form();
        $form->setWorkarea($workarea)
            ->setTag($tag)
            ->setContent($participant->toArray())
            ->setUpdatedAt(new \DateTime('now'));
        return $form;
    }


    /**
     * @param Workarea $workarea
     * @param Participant $participant
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Workarea $workarea, Participant &$participant)
    {
        $tag = $this->tagRepository->fetch('participant');
        $em=$this->formRepository->getEntityManager();
        $form=$this->fetchForm($workarea,$tag,$participant);
        $em->persist($form);
        $em->flush();
        $participant->setId($form->getId());
    }



}