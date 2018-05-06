<?php

namespace App\Entity\Competition;

use Doctrine\ORM\Mapping as ORM;

/**
 * Competition
 *
 * @ORM\Table(name="competition")
 * @ORM\Entity(repositoryClass="App\Repository\Competition\CompetitionRepository")
 */
class Competition
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
     * @ORM\Column(name="name", type="string", length=80, nullable=false)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", length=1, nullable=true, options={"fixed"=true})
     */
    private $status;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="start", type="datetime", nullable=true)
     */
    private $start;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end", type="datetime", nullable=true)
     */
    private $end;

    /**
     * @var string|null
     *
     * @ORM\Column(name="timing", type="json", nullable=true)
     */
    private $timing;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;


    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
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
     * @return Competition
     */
    public function setId(int $id): Competition
    {
        $this->id = $id;
        return $this;
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
    public function setName(string $name): Competition
    {
        $this->name = $name;
        return $this;
    }


    /**
     * @return null|string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param null|string $status
     * @return Competition
     */
    public function setStatus(?string $status): Competition
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    /**
     * @param \DateTime|null $start
     * @return Competition
     */
    public function setStart(?\DateTime $start): Competition
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime|null $end
     * @return Competition
     */
    public function setEnd(?\DateTime $end): Competition
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getTiming(): ?array
    {
        return $this->timing?json_decode($this->timing):null;
    }

    /**
     * @param array|null $timing
     * @return Competition
     */
    public function setTiming(?array $timing): Competition
    {
        $this->timing = json_encode($timing);
        return $this;
    }
}
