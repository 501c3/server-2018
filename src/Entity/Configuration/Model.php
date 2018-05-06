<?php

namespace App\Entity\Configuration;

use Doctrine\ORM\Mapping as ORM;

/**
 * Model
 *
 * @ORM\Table(name="model", indexes={@ORM\Index(name="idx_model_name", columns={"name"}),
 *                                   @ORM\Index(name="idx_model_id", columns={"model_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Configuration\ModelRepository")
 */
class Model
{
    /**
     * @var int
     *
     * @ORM\Column(name="model_id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     */
    private $modelId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=40, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", length=16777215, nullable=false)
     */
    private $text;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $updatedAt = 'CURRENT_TIMESTAMP';

    /**
     * @return int
     */
    public function getModelId(): int
    {
        return $this->modelId;
    }

    /**
     * @param int $modelId
     * @return Model
     */
    public function setModelId(int $modelId): Model
    {
        $this->modelId = $modelId;
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
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return Model
     */
    public function setText(string $text): Model
    {
        $this->text = $text;
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
     * @return Model
     */
    public function setUpdatedAt(?\DateTime $updatedAt): Model
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
