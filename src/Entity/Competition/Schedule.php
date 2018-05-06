<?php

namespace App\Entity\Competition;

use Doctrine\ORM\Mapping as ORM;

/**
 * Schedule
 *
 * @ORM\Table(name="schedule", indexes={@ORM\Index(name="fk_schedule_competition1_idx", columns={"competition_id"}), @ORM\Index(name="fk_schedule_session1_idx", columns={"session_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Competition\ScheduleRepository")
 */
class Schedule
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="when", type="datetime", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $when;

    /**
     * @var int
     *
     * @ORM\Column(name="heat", type="integer", nullable=false)
     */
    private $heat;

    /**
     * @var integer
     *
     * @ORM\Column(name="round", type="integer", nullable=false)
     */
    private $round;

    /**
     * @var integer|null
     *
     * @ORM\Column(name="groups", type="integer", nullable=true)
     */
    private $groups;

    /**
     * @var integer|null
     *
     * @ORM\Column(name="players", type="integer", nullable=true)
     */
    private $players;

    /**
     * @var integer|null
     *
     * @ORM\Column(name="dances", type="integer", nullable=true)
     */
    private $dances;

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
     * @var \App\Entity\Competition\Session
     *
     * @ORM\ManyToOne(targetEntity="Session")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="session_id", referencedColumnName="id")
     * })
     */
    private $session;

    /**
     * @return \DateTime
     */
    public function getWhen(): \DateTime
    {
        return $this->when;
    }

    /**
     * @param \DateTime $when
     * @return Schedule
     */
    public function setWhen(\DateTime $when): Schedule
    {
        $this->when = $when;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeat(): int
    {
        return $this->heat;
    }

    /**
     * @param int $heat
     * @return Schedule
     */
    public function setHeat(int $heat): Schedule
    {
        $this->heat = $heat;
        return $this;
    }

    /**
     * @param int $round
     * @return Schedule
     */
    public function setRound(int $round): Schedule
    {
        $this->round = $round;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getGroups(): ?bool
    {
        return $this->groups;
    }

    /**
     * @param bool|null $groups
     * @return Schedule
     */
    public function setGroups(?bool $groups): Schedule
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPlayers(): ?int
    {
        return $this->players;
    }

    /**
     * @param int|null $players
     * @return Schedule
     */
    public function setPlayers(?int $players): Schedule
    {
        $this->players = $players;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDances(): ?int
    {
        return $this->dances;
    }

    /**
     * @param int|null $dances
     * @return Schedule
     */
    public function setDances(?int $dances): Schedule
    {
        $this->dances = $dances;
        return $this;
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
     * @return Schedule
     */
    public function setCompetition(Competition $competition): Schedule
    {
        $this->competition = $competition;
        return $this;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @param Session $session
     * @return Schedule
     */
    public function setSession(Session $session): Schedule
    {
        $this->session = $session;
        return $this;
    }
}
