<?php

namespace App\Entity\Competition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Number
 *
 * @ORM\Table(name="number", indexes={@ORM\Index(name="fk_number_competition1_idx", columns={"competition_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Competition\NumberRepository")
 */
class Number
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="when", type="datetime", nullable=true)
     */
    private $when;

    /**
     * @var \App\Entity\Competition\Competition
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition\Competition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_id", referencedColumnName="id")
     * })
     */
    private $competition;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Competition\Person", mappedBy="number")
     */
    private $person;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->person = new ArrayCollection();
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
     * @return Number
     */
    public function setId(int $id): Number
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getWhen(): ?\DateTime
    {
        return $this->when;
    }

    /**
     * @param \DateTime|null $when
     * @return Number
     */
    public function setWhen(?\DateTime $when): Number
    {
        $this->when = $when;
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
     * @return Number
     */
    public function setCompetition(Competition $competition): Number
    {
        $this->competition = $competition;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getPerson(): Collection
    {
        return $this->person;
    }

    /**
     * @param Collection $person
     * @return Number
     */
    public function setPerson(Collection $person): Number
    {
        $this->person = $person;
        return $this;
    }

}
