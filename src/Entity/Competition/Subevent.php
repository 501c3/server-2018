<?php

namespace App\Entity\Competition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Subevent
 *
 * @ORM\Table(name="subevent",
 *            indexes={ @ORM\Index(name="fk_subevent_model1_idx", columns={"model_id"}),
 *                      @ORM\Index(name="fk_subevent_competition1_idx", columns={"competition_id"}),
 *                      @ORM\Index(name="idx_heat_number", columns={"competition_id", "heat"})})
 * @ORM\Entity(repositoryClass="App\Repository\Competition\SubeventRepository")
 */
class Subevent
{
    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $sequence;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="event_id", type="integer", nullable=false)
     */
    private $eventId;

    /**
     * @var array
     *
     * @ORM\Column(name="value", type="json", nullable=false)
     */
    private $value;

    /**
     * @var int|null
     *
     * @ORM\Column(name="heat", type="integer", nullable=true)
     */
    private $heat;

    /**
     * @var \App\Entity\Competition\Competition
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition\Competition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_id", referencedColumnName="id")
     * })
     */
    private $competition;

    /**
     * @var \App\Entity\Competition\Model
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition\Model")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     * })
     */
    private $model;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Competition\Team", inversedBy="subeventCompetition")
     * @ORM\JoinTable(name="subevent_has_team",
     *   joinColumns={
     *     @ORM\JoinColumn(name="subevent_competition_id", referencedColumnName="competition_id"),
     *     @ORM\JoinColumn(name="subevent_sequence", referencedColumnName="sequence"),
     *     @ORM\JoinColumn(name="subevent_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     *   }
     * )
     */
    private $team;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->team = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * @param int $sequence
     * @return Subevent
     */
    public function setSequence(int $sequence): Subevent
    {
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Subevent
     */
    public function setId(int $id): Subevent
    {
        $this->id = $id;
        return $this;
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
     * @return Subevent
     */
    public function setEventId(int $eventId): Subevent
    {
        $this->eventId = $eventId;
        return $this;
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return $this->value;
    }

    /**
     * @param array $value
     * @return Subevent
     */
    public function setValue($value): Subevent
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeat(): ?int
    {
        return $this->heat;
    }

    /**
     * @param int|null $heat
     * @return Subevent
     */
    public function setHeat(?int $heat): Subevent
    {
        $this->heat = $heat;
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
     * @return Subevent
     */
    public function setCompetition(Competition $competition): Subevent
    {
        $this->competition = $competition;
        return $this;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param Model $model
     * @return Subevent
     */
    public function setModel(Model $model): Subevent
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getTeam(): Collection
    {
        return $this->team;
    }

    /**
     * @param Collection $team
     * @return Subevent
     */
    public function setTeam(Collection $team): Subevent
    {
        $this->team = $team;
        return $this;
    }

}
