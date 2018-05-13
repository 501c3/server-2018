<?php

namespace App\Entity\Competition;

use Doctrine\ORM\Mapping as ORM;

/**
 * Iface
 *
 * @ORM\Table(name="iface", indexes={@ORM\Index(name="fk_iface_competition1_idx", columns={"competition_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Competition\IfaceRepository")
 */
class Iface
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="setup", type="json", nullable=true)
     */
    private $setup;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mapping", type="json", nullable=true)
     */
    private $mapping;

    /**
     * @var Competition
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="App\Entity\Competition\Competition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_id", referencedColumnName="id")
     * })
     */
    private $competition;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Iface
     */
    public function setName(string $name): Iface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getSetup(): ?array
    {
        return json_decode($this->setup);
    }

    /**
     * @param array|null $setup
     * @return Iface
     */
    public function setSetup(?array $setup): Iface
    {
        $this->setup = json_encode($setup);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getMapping(): ?array
    {
        return json_decode($this->mapping);
    }

    /**
     * @param array|null $mapping
     * @return Iface
     */
    public function setMapping(?array $mapping): Iface
    {
        $this->mapping = json_encode($mapping);
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
     * @return Iface
     */
    public function setCompetition(Competition $competition): Iface
    {
        $this->competition = $competition;
        return $this;
    }
}
