<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/21/18
 * Time: 5:33 PM
 */

namespace App\Entity\Sales\Iface;


use App\Doctrine\Iface\Classify;
use App\Entity\Sales\Tag;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;

class Player
{
    private $participants=[];

    /**
     * @var Classify
     */
    private $classify;
    /**
     * @var FormRepository
     */
    private $formRepository;

    private $tag;

    private $qualifications ;

    /**
     * Player constructor.
     * @param Classify $classify
     * @param FormRepository $formRepository
     * @param TagRepository $tagRepository
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function __construct(FormRepository $formRepository, Tag $tag)
    {
        $this->formRepository = $formRepository;
        $this->tag = $tag;
        $this->qualifications = new ArrayCollection();
    }

    public function addParticipant(Participant $p):Player
    {
        array_push($this->participants, $p);
        return $this;
    }

    public function addQualification(Qualification $qualification)
    {
        $value=$qualification->get('genre');
        $this->qualifications->set($value->getName(),$qualification);
        return $this;
    }

    public function getQualification(string $genre){
        return $this->qualifications->get($genre);
    }


    /**
     * @param array $precs
     * @return int
     *
     * $records are referenced by their form id.
     * returns form.id for player.id and event selection
     * @throws \App\Doctrine\Iface\ClassifyException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(array $precs): int
    {
        $participants=[];
        foreach($precs as $id) {
           $participant = new Participant($this->classify->getValueById(), $this->classify->getModelById(),
                                          $this->formRepository);
           $participant->read($id);
           array_push($participants, $participant);
        }

    }


    public function read()
    {
        return 0;
    }

    /**
     * @param array $events
     * @return array
     *
     * $players are referenced by their form id.
     * returns events selected by model.id and event.id
     */
    public function update(array $events): ?array
    {
        return null;
    }

    /**
     * @param int $id
     *
     * $id is the form.id to be deleted
     */
    public function delete(int $id)
    {

    }

}