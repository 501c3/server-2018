<?php

namespace App\Entity\Competition;

use Doctrine\ORM\Mapping as ORM;

/**
 * Placement
 *
 * @ORM\Table(name="placement", indexes={@ORM\Index(name="fk_placement_competition1_idx", columns={"competition_id"}),
 *                                       @ORM\Index(name="fk_placement_team1_idx", columns={"team_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Competition\PlacementRepository")
 */
class Placement
{
    /**
     * @var int
     *
     * @ORM\Column(name="heat", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $heat;

    /**
     * @var int
     *
     * @ORM\Column(name="model_id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $modelId;

    /**
     * @var int
     *
     * @ORM\Column(name="event_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $eventId;

    /**
     * @var int
     *
     * @ORM\Column(name="place", type="smallint", nullable=false)
     */
    private $place;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="when", type="datetime", nullable=false)
     */
    private $when;

    /**
     * @var Competition
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="App\Entity\Competition\Competition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_id", referencedColumnName="id")
     * })
     */
    private $competition;

    /**
     * @var Team
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="App\Entity\Competition\Team")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     * })
     */
    private $team;

    /**
     * @return int
     */
    public function getHeat(): int
    {
        return $this->heat;
    }

    /**
     * @param int $heat
     */
    public function setHeat(int $heat): void
    {
        $this->heat = $heat;
    }

    /**
     * @return int
     */
    public function getModelId(): int
    {
        return $this->modelId;
    }

    /**
     * @param int $modelId
     */
    public function setModelId(int $modelId): void
    {
        $this->modelId = $modelId;
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }

    /**
     * @param int $eventId
     */
    public function setEventId(int $eventId): void
    {
        $this->eventId = $eventId;
    }

    /**
     * @return int
     */
    public function getPlace(): int
    {
        return $this->place;
    }

    /**
     * @param int $place
     */
    public function setPlace(int $place): void
    {
        $this->place = $place;
    }

    /**
     * @return \DateTime
     */
    public function getWhen(): \DateTime
    {
        return $this->when;
    }

    /**
     * @param \DateTime $when
     */
    public function setWhen(\DateTime $when): void
    {
        $this->when = $when;
    }

    /**
     * @return Competition
     */
    public function getCompetition(): Competition
    {
        return $this->competition;
    }

    /**
     * @param Competition $competition
     */
    public function setCompetition(Competition $competition): void
    {
        $this->competition = $competition;
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @param Team $team
     */
    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }


}
