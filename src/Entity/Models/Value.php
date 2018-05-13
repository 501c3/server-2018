<?php

namespace App\Entity\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Value
 *
 * @ORM\Table(name="value", indexes={@ORM\Index(name="fk_value_domain1_idx", columns={"domain_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Models\ValueRepository")
 */
class Value
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="abbr", type="string", length=4, nullable=false)
     */
    private $abbr;

    /**
     * @var int
     *
     * @ORM\Column(name="ord", type="smallint", nullable=false)
     */
    private $ord;

    /**
     * @var \App\Entity\Models\Domain
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Models\Domain")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     * })
     */
    private $domain;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Models\Event", mappedBy="value")
     */
    private $event;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Models\Model", mappedBy="value")
     */
    private $model;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Models\Player", mappedBy="value")
     */
    private $player;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Models\Subevent", mappedBy="value")
     */
    private $subevent;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->event = new ArrayCollection();
        $this->model = new ArrayCollection();
        $this->player = new ArrayCollection();
        $this->subevent = new ArrayCollection();
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
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): Value
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAbbr(): string
    {
        return $this->abbr;
    }

    /**
     * @param string $abbr
     */
    public function setAbbr(string $abbr): Value
    {
        $this->abbr = $abbr;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrd(): int
    {
        return $this->ord;
    }

    /**
     * @param int $ord
     */
    public function setOrd(int $ord): Value
    {
        $this->ord = $ord;
        return $this;
    }

    /**
     * @return Domain
     */
    public function getDomain(): Domain
    {
        return $this->domain;
    }

    /**
     * @param Domain $domain
     */
    public function setDomain(Domain $domain): Value
    {
        $this->domain = $domain;
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
     */
    public function setEvent(Collection $event): Value
    {
        $this->event = $event;
    }

    /**
     * @return Collection
     */
    public function getModel(): Collection
    {
        return $this->model;
    }

    /**
     * @param Collection $model
     */
    public function setModel(Collection $model): Value
    {
        $this->model = $model;
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
     */
    public function setPlayer(Collection $player): Value
    {
        $this->player = $player;
    }

    /**
     * @return Collection
     */
    public function getSubevent(): Collection
    {
        return $this->subevent;
    }

    /**
     * @param Collection $subevent
     */
    public function setSubevent(Collection $subevent): Value
    {
        $this->subevent = $subevent;
    }

    public function __toString()
    {
        return strval($this->id);
    }
}
