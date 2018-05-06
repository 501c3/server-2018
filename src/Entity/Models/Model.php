<?php

namespace App\Entity\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Model
 *
 * @ORM\Table(name="model", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Models\ModelRepository")
 */
class Model
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
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;


     /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Models\Value", inversedBy="model")
     * @ORM\JoinTable(name="model_has_value",
     *   joinColumns={
     *     @ORM\JoinColumn(name="model_id", referencedColumnName="id")
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
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
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
     * @return Model
     */
    public function setValue(Collection $value): Model
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param Value $element
     * @return Model
     */
    public function addValue(Value $element): Model
    {
        $this->value->add($element);
        return $this;
    }
}
