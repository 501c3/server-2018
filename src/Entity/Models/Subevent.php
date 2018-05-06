<?php

namespace App\Entity\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Subevent
 *
 * @ORM\Table(name="subevent", indexes={@ORM\Index(name="fk_subevent_event1_idx", columns={"event_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Models\SubeventRepository")
 */
class Subevent
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
     * @var \App\Entity\Models\Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Models\Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    private $event;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Models\Value", inversedBy="subevent")
     * @ORM\JoinTable(name="subevent_has_value",
     *   joinColumns={
     *     @ORM\JoinColumn(name="subevent_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="value_id", referencedColumnName="id")
     *   }
     * )
     */
    private $value;

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
     * @param int $id
     * @return Subevent
     */
    public function setId(int $id): Subevent
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     * @return Subevent
     */
    public function setEvent(Event $event): Subevent
    {
        $this->event = $event;
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
     * @return Subevent
     */
    public function setValue(Collection $value): Subevent
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param Value $element
     * @return Subevent
     */
    public function addValue(Value $element): Subevent
    {
        $this->value->add($element);
        return $this;
    }

}
