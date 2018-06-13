<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/21/18
 * Time: 3:42 PM
 */

namespace App\Entity\Sales\Iface;

use App\Entity\Models\Value;
use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;

class Participant
{
    private $id;

    /** @var string */
    private $first;

    /** @var string */
    private $last;

    /** @var integer */
    private $years;

    /** @var string */
    private $sex;

    /**
     * @var int|Value
     * Professional or Amateur
     */
    private $typeA;

    /**
     * @var int|Value
     * Teacher or Student
     */
    private $typeB;

    /** @var string */
    private $status;

    /** @var ArrayCollection|null*/
    private $genreProficiency;

    /** @var array|null  */
    private $valueById;

    /**@var array*/
    private $modelById;

    private $models=[];
    /**
     * @var FormRepository
     */
    private $formRepository;
    /**
     * @var Tag
     */
    private $tag;

    /**
     * Participant constructor.
     * @param array $valueById
     * @param array $modelById
     * @param FormRepository $formRepository
     * @param Tag $tag
     */
    public function __construct(array $valueById,array $modelById,FormRepository $formRepository,Tag $tag)
    {
        $this->valueById = $valueById;
        $this->modelById = $modelById;
        $this->genreProficiency = new ArrayCollection();
        $this->formRepository = $formRepository;
        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function getFirst(): string
    {
        return $this->first;
    }

    /**
     * @param string $first
     * @return Participant
     */
    public function setFirst(string $first): Participant
    {
        $this->first = $first;
        return $this;
    }

    /**
     * @return string
     */
    public function getLast(): string
    {
        return $this->last;
    }

    /**
     * @param string $last
     * @return Participant
     */
    public function setLast(string $last): Participant
    {
        $this->last = $last;
        return $this;
    }

    public function getName() : string
    {
        return $this->first.' '.$this->last;
    }

    /**
     * @return int
     */
    public function getYears(): int
    {
        return $this->years;
    }

    /**
     * @param int $years
     * @return Participant
     */
    public function setYears(int $years): Participant
    {
        $this->years = $years;
        return $this;
    }

    /**
     * @return string
     */
    public function getSex(): string
    {
        return $this->sex;
    }

    /**
     * @param string $sex
     * @return Participant
     */
    public function setSex(string $sex): Participant
    {
        $this->sex = $sex;
        return $this;
    }

    /**
     * @return Value
     */
    public function getTypeA(): Value
    {
        return intval($this->typeA)?$this->valueById[$this->typeA]:$this->typeA;
    }

    /**
     * @return Value
     */
    public function getTypeB(): Value
    {
        return intval($this->typeB)?$this->valueById[$this->typeB]:$this->typeB;
    }



    /**
     * @param int|Value $type
     * @return Participant
     */
    public function setTypeA($type): Participant
    {
        $this->typeA=$type;
        return $this;
    }

    /**
     * @param int|Value $type
     * @return Participant
     */
    public function setTypeB($type): Participant
    {
        $this->typeB=$type;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Participant
     */
    public function setStatus(string $status): Participant
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param int $genre
     * @param int $proficiency
     * @return Participant

     */
    public function addGenreProficiency(int $genre,int $proficiency) : Participant
    {
        $this->genreProficiency[$genre] = $proficiency;
        return $this;
    }

    public function getGenreProficiency($genreId=null)
    {
        if($genreId) {
            return $this->genreProficiency[$genreId];
        }
        return $this->genreProficiency;
    }

    /**
     * @param int $modelId
     * @return Participant
     */
    public function addModel(int $modelId): Participant
    {
        array_push($this->models, $modelId);
        return $this;
    }

    private function preCreate()
    {
        return ['first'=>$this->first,
                'last' => $this->last,
                'sex'=>$this->sex,
                'typeA'=>$this->typeA,
                'typeB'=>$this->typeB,
                'genreProficiency'=>$this->genreProficiency->toArray(),
                'models'=> $this->models->toArray()];
    }

    private function preUpdate()
    {
        return ['id'=>$this->id,
                'first'=>$this->first,
                'last' => $this->last,
                'sex'=>$this->sex,
                'typeA'=>$this->typeA,
                'typeB'=>$this->typeB,
                'genreProficiency'=>$this->genreProficiency->toArray(),
                'models'=> $this->models->toArray()];
    }


    /**
     * @param array $participant
     * @return int
     *
     * input: participant data
     * return: form.id
     *
     */
    public function create(array $participant): int
    {
        $em=$this->formRepository->getEntityManager();
        /** @var Form $form */
        $form = new Form();
        $form->setTag($this->tag)
             ->setContent($participant);
        $em->persist($form);
        $em->flush();
        return $form->getId();
    }

    /**
     * setup information
     * @param int|null $id
     * @return array
     */
    public function read(int $id=null)
    {
        return $id?$this->preUpdate():[];
    }

    /**
     * @param array $participant
     * input: participant data to replace at id
     * return form.id
     */
    public function update(array $participant): int
    {
        return 0;
    }

    /**
     * @param int $id
     */
    public function delete(int $id)
    {
        return 0;
    }


}


