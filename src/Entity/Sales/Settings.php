<?php

namespace App\Entity\Sales;

use Doctrine\ORM\Mapping as ORM;

/**
 * Settings
 *
 * @ORM\Table(name="settings", indexes={@ORM\Index(name="fk_settings_channel1_idx", columns={"channel_id"}), @ORM\Index(name="fk_settings_tag1_idx", columns={"tag_id"}), @ORM\Index(name="IDX_E545A0C537BAC19A", columns={"processor_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\SettingsRepository")
 */
class Settings
{
    /**
     * @var string
     *
     * @ORM\Column(name="data", type="json", nullable=false)
     */
    private $data;

    /**
     * @var \App\Entity\Sales\Channel
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
     * @var \App\Entity\Sales\Processor
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="App\Entity\Sales\Processor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="processor_id", referencedColumnName="id")
     * })
     */
    private $processor;

    /**
     * @var \App\Entity\Sales\Tag
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
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return Settings
     */
    public function setData(string $data): Settings
    {
        $this->data = $data;
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
     * @return Settings
     */
    public function setChannel(Channel $channel): Settings
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return Processor
     */
    public function getProcessor(): Processor
    {
        return $this->processor;
    }

    /**
     * @param Processor $processor
     * @return Settings
     */
    public function setProcessor(Processor $processor): Settings
    {
        $this->processor = $processor;
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
     * @return Settings
     */
    public function setTag(Tag $tag): Settings
    {
        $this->tag = $tag;
        return $this;
    }


}
