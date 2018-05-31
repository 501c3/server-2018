<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/23/18
 * Time: 8:31 AM
 */

namespace App\Doctrine\Iface;

use App\Entity\Competition\Competition;
use App\Entity\Sales\Client\Participant;
use App\Entity\Sales\Client\Team;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\IfaceRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;
use Doctrine\Common\Collections\ArrayCollection;

class Classify
{

    /** @var ArrayCollection  */
    private $collection;

    /** @var Competition */
    private $competition;
    /**
     * @var CompetitionRepository
     */
    private $competitionRepository;
    /**
     * @var ModelRepository
     */
    private $modelRepository;
    /**
     * @var ValueRepository
     */
    private $valueRepository;

    /**
     * @var IfaceRepository
     */
    private $ifaceRepository;


    public function __construct(
        CompetitionRepository $competitionRepository,
        ModelRepository $modelRepository,
        IfaceRepository $ifaceRepository,
        ValueRepository $valueRepository
    )
    {
        $this->competitionRepository = $competitionRepository;
        $this->modelRepository = $modelRepository;
        $this->valueRepository = $valueRepository;
        $this->ifaceRepository = $ifaceRepository;
    }

    public function couple(Participant $p1, Participant $p2) : Team
    {
        return new Team();
    }

    public function solo(Participant $p) : Team
    {
        return new Team();
    }
}