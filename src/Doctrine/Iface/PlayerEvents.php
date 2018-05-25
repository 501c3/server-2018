<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/20/18
 * Time: 11:33 PM
 */

namespace App\Doctrine\Iface;


use App\Entity\Sales\Client\Participant;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\EventRepository;
use App\Repository\Competition\IfaceRepository;
use App\Repository\Competition\PlayerRepository;
use App\Repository\Competition\ModelRepository;


class PlayerEvents
{
    /**
     * @var CompetitionRepository
     */
    private $competitionRepository;
    /**
     * @var IfaceRepository
     */
    private $ifaceRepository;
    /**
     * @var ModelRepository
     */
    private $modelRepository;
    /**
     * @var PlayerRepository
     */
    private $playerRepository;
    /**
     * @var EventRepository
     */
    private $eventRepository;

    public function __construct(
        CompetitionRepository $competitionRepository,
        IfaceRepository $ifaceRepository,
        ModelRepository $modelRepository,
        PlayerRepository $playerRepository,
        EventRepository $eventRepository
    )
    {
        $this->competitionRepository = $competitionRepository;
        $this->ifaceRepository = $ifaceRepository;
        $this->modelRepository = $modelRepository;
        $this->playerRepository = $playerRepository;
        $this->eventRepository = $eventRepository;
    }

    public function couple(Participant $p1, Participant $p2) : array
    {
        return [];
    }

    public function individual(Participant $p) : array
    {
        return [];
    }

}