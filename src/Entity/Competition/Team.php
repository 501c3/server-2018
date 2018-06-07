<?php

namespace App\Entity\Competition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table(name="team")
 * @ORM\Entity(repositoryClass="App\Repository\Competition\TeamRepository")
 */
class Team
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
     * @ORM\Column(name="status", type="string", length=1, nullable=false, options={"fixed"=true})
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Competition\Subevent", mappedBy="team")
     */
    private $subeventCompetition;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Competition\Person", inversedBy="team")
     * @ORM\JoinTable(name="team_has_person",
     *   joinColumns={
     *     @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     *   }
     * )
     */
    private $person;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->subeventCompetition = new ArrayCollection();
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
     * @return Team
     */
    public function setId(int $id): Team
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Team
     */
    public function setStatus(string $status): Team
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Team
     */
    public function setCreatedAt(\DateTime $createdAt): Team
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getSubeventCompetition(): Collection
    {
        return $this->subeventCompetition;
    }

    /**
     * @param Collection $subeventCompetition
     * @return Team
     */
    public function setSubeventCompetition(Collection $subeventCompetition): Team
    {
        $this->subeventCompetition = $subeventCompetition;
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
     * @return Team
     */
    public function setPerson(Collection $person): Team
    {
        $this->person = $person;
        return $this;
    }

}
