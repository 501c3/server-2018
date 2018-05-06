<?php

namespace App\Entity\Configuration;

use Doctrine\ORM\Mapping as ORM;

/**
 * Competition
 *
 * @ORM\Table(name="competition", indexes={@ORM\Index(name="idx_competition_name", columns={"name"}), @ORM\Index(name="idx_competition_id", columns={"competition_id"})})
 * @ORM\Entity
 */
class Competition
{
    /**
     * @var int
     *
     * @ORM\Column(name="competition_id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $competitionId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=40, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="sequence", type="text", length=16777215, nullable=false)
     */
    private $sequence;

    /**
     * @var string|null
     *
     * @ORM\Column(name="interface", type="text", length=16777215, nullable=true)
     */
    private $interface;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $updatedAt = 'CURRENT_TIMESTAMP';

    /**
     * @return int
     */
    public function getCompetitionId(): int
    {
        return $this->competitionId;
    }

    /**
     * @param int $competitionId
     * @return Competition
     */
    public function setCompetitionId(int $competitionId): Competition
    {
        $this->competitionId = $competitionId;
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
     * @return Competition
     */
    public function setName(string $name): Competition
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getSequence(): string
    {
        return $this->sequence;
    }

    /**
     * @param string $sequence
     * @return Competition
     */
    public function setSequence(string $sequence): Competition
    {
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getInterface(): ?string
    {
        return $this->interface;
    }

    /**
     * @param null|string $interface
     * @return Competition
     */
    public function setInterface(?string $interface): Competition
    {
        $this->interface = $interface;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     * @return Competition
     */
    public function setUpdatedAt(?\DateTime $updatedAt): Competition
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


}
