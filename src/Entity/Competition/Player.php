<?php

namespace App\Entity\Competition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table(name="player", indexes={@ORM\Index(name="fk_player_model1_idx", columns={"model_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Competition\PlayerRepository")
 */
class Player
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(name="value", type="json", nullable=false)
     */
    private $value;

    /**
     * @var Model
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Competition\Event", inversedBy="player")
     * @ORM\JoinTable(name="player_has_event1",
     *   joinColumns={
     *     @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     *   }
     * )
     */
    private $event;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->event = new ArrayCollection();
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
     * @return Player
     */
    public function setId(int $id): Player
    {
        $this->id = $id;
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
     * @return Player
     */
    public function setValue(array $value): Player
    {
        $this->value = $value;
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
     * @return Player
     */
    public function setModel(Model $model): Player
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getEvent(): Collection
    {
        return $this->event;
    }

    /**
     * @param Collection $event
     * @return Player
     */
    public function setEvent(Collection $event): Player
    {
        $this->event = $event;
        return $this;
    }

}
