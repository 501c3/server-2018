<?php

namespace App\Entity\Competition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event", indexes={@ORM\Index(name="fk_event_model1_idx", columns={"model_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Competition\EventRepository")
 */
class Event
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
     * @var string
     *
     * @ORM\Column(name="tag", type="string", length=1, nullable=false, options={"fixed"=true})
     */
    private $tag;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="json", nullable=false)
     */
    private $value;

    /**
     * @var \App\Entity\Competition\Model
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="App\Entity\Competition\Model")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     * })
     */
    private $model;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Competition\Player", mappedBy="eventModel")
     */
    private $playerModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->playerModel = new ArrayCollection();
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
     * @return Event
     */
    public function setId(int $id): Event
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     * @return Event
     */
    public function setTag(string $tag): Event
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return json_decode($this->value);
    }

    /**
     * @param array $value
     * @return Event
     */
    public function setValue(array $value): Event
    {
        $this->value = json_encode($value);
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
     * @return Event
     */
    public function setModel(Model $model): Event
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getPlayerModel(): Collection
    {
        return $this->playerModel;
    }

    /**
     * @param Collection $playerModel
     * @return Event
     */
    public function setPlayerModel(Collection $playerModel): Event
    {
        $this->playerModel = $playerModel;
        return $this;
    }


}
