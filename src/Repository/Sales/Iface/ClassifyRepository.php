<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/20/18
 * Time: 9:14 AM
 */

namespace App\Repository\Sales\Iface;


use App\Entity\Sales\Iface\Classify;
use App\Repository\Competition\IfaceRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\EventRepository;
use App\Repository\Models\ValueRepository;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\TagRepository;

class ClassifyRepository
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
     * @var IfaceRepository
     */
    private $ifaceRepository;
    /**
     * @var PlayerRepository
     */
    private $playerRepository;
    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var TagRepository
     */
    private $tagRepository;
    /**
     * @var FormRepository
     */
    private $formRepository;

    public function __construct(
        ValueRepository $valueRepository,
        ModelRepository $modelRepository,
        IfaceRepository $ifaceRepository,
        PlayerRepository $playerRepository,
        EventRepository $eventRepository,
        FormRepository $formRepository,
        TagRepository $tagRepository
    )
    {
        $this->valueRepository = $valueRepository;
        $this->modelRepository = $modelRepository;
        $this->ifaceRepository = $ifaceRepository;
        $this->playerRepository = $playerRepository;
        $this->eventRepository = $eventRepository;
        $this->tagRepository = $tagRepository;
        $this->formRepository = $formRepository;
    }

    public function fetchClassify(Channel $channel)
    {
        $name=$channel->getName();
        $classifierName = str_replace(" ","",$name).'Classify';
        /** @var Classify $classify*/
        $classify =  new $classifierName();
        $classify->setDomainValueHash($this->valueRepository->fetchDomainValueHash())
                 ->setValueById($this->valueRepository->fetchAllValuesById());


    }

}