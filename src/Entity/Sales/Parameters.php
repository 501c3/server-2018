<?php

namespace App\Entity\Sales;


use Doctrine\ORM\Mapping as ORM;

/**
 * Parameters
 *
 * @ORM\Table(name="parameters", indexes={@ORM\Index(name="fk_parameters_channel1_idx", columns={"channel_id"}), @ORM\Index(name="fk_parameters_tag1_idx", columns={"tag_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\ParametersRepository")
 */
class Parameters
{
    /**
     * @var string
     *
     * @ORM\Column(name="`key`", type="string", length=45, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="`value`", type="string", length=80, nullable=false)
     */
    private $value;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var Channel
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="App\Entity\Sales\Channel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     * })
     */
    private $channel;

    /**
     * @var Tag
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="App\Entity\Sales\Tag")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     * })
     */
    private $tag;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Parameters
     */
    public function setKey(string $key): Parameters
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return Parameters
     */
    public function setValue(string $value): Parameters
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return Parameters
     */
    public function setCreatedAt(?\DateTime $createdAt): Parameters
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     * @return Parameters
     */
    public function setChannel(Channel $channel): Parameters
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return Tag
     */
    public function getTag(): Tag
    {
        return $this->tag;
    }

    /**
     * @param Tag $tag
     * @return Parameters
     */
    public function setTag(Tag $tag): Parameters
    {
        $this->tag = $tag;
        return $this;
    }
}
