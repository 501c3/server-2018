<?php

namespace App\Entity\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_event_model1_idx", columns={"model_id"}), @ORM\Index(name="fk_event_tag1_idx", columns={"tag_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Models\EventRepository")
 */
class Event
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
     * @var \App\Entity\Models\Tag
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Models\Tag")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     * })
     */
    private $tag;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Models\Value", inversedBy="event")
     * @ORM\JoinTable(name="event_has_value",
     *   joinColumns={
     *     @ORM\JoinColumn(name="event_id", referencedColumnName="id")
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Models\Player", mappedBy="event")
     */
    private $player;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * @return Event
     */
    public function setModel(Model $model): Event
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return Tag
     */
    public function getTag(): Tag
    {
        return $this->tag;
    }

    /**
     * @param Tag $tag
     * @return Event
     */
    public function setTag(Tag $tag): Event
    {
        $this->tag = $tag;
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
     * @param Collection $value
     * @return Event
     */
    public function setValue(Collection $value): Event
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getPlayer(): Collection
    {
        return $this->player;
    }

    /**
     * @param Collection $player
     * @return Event
     */
    public function setPlayer(Collection $player): Event
    {
        $this->player = $player;
        return $this;
    }

    /**
     * @param Player $player
     * @return Event
     */
    public function addPlayer(Player $player): Event
    {
        $this->player->add($player);
        return $this;
    }

    public function addValue(Value $element): Event
    {
        $this->value->add($element);
        return $this;
    }

}
