<?php

namespace App\Entity\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table(name="player", indexes={@ORM\Index(name="fk_player_model1_idx", columns={"model_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Models\PlayerRepository")
 */
class Player
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \App\Entity\Models\Model
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Models\Model")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     * })
     */
    private $model;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Models\Value", inversedBy="player")
     * @ORM\JoinTable(name="player_has_value",
     *   joinColumns={
     *     @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="value_id", referencedColumnName="id")
     *   }
     * )
     */
    private $value;


    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Models\Event", inversedBy="player")
     * @ORM\JoinTable(name="player_has_event",
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
        $this->value = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
    public function getValue(): Collection
    {
        return $this->value;
    }

    /**
     * @param Value $value
     */
    public function addValue(Value $element): Player
    {
        $this->value->add($element);
        return $this;
    }

    /*
     * TODO:  Remove after verification of tests
     *
     * @param Collection $value
     * @return Player
     */
    /*public function setValue(Collection $value): Player
    {
        $this->value = $value;
        return $this;
    }*/

    /**
     * @return Collection
     */
    public function getEvent(): Collection
    {
        return $this->event;
    }

    /*
     * TODO: Remove
     *
     * @param Collection $event
     * @return Player
     */
    /*public function setEvent(Collection $event): Player
    {
        $this->event = $event;
        return $this;
    }*/


    public function addEvent(Event $element): Player
    {
        $this->event->add($element);
        return $this;
    }

}
