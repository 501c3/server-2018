<?php

namespace App\Entity\Competition;

use Doctrine\ORM\Mapping as ORM;

/**
 * Model
 *
 * @ORM\Table(name="model")
 * @ORM\Entity(repositoryClass="App\Repository\Competition\ModelRepository")
 */
class Model
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
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
     * @ORM\Column(name="playerlookup", type="json", nullable=false)
     */
    private $playerlookup;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @param int $id
     * @return Model
     */
    public function setId(int $id): Model
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
     * @return Model
     */
    public function setName(string $name): Model
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getPlayerlookup(): array
    {
        return json_decode($this->playerlookup);
    }

    /**
     * @param array $playerlookup
     */
    public function setPlayerlookup(array $playerlookup): void
    {
        $this->playerlookup = json_encode($playerlookup);
    }


}
