<?php
namespace App\Entity\Sales;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Workarea
 *
 * @ORM\Table(name="workarea", indexes={@ORM\Index(name="fk_workarea_channel1_idx", columns={"channel_id"}),
 *                                      @ORM\Index(name="fk_workarea_tag1_idx", columns={"tag_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\WorkareaRepository")
 */
class Workarea
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="token", type="text", length=255, nullable=true)
     */
    private $token;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="processed_at", type="datetime", nullable=true)
     */
    private $processedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \App\Entity\Sales\Channel
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Channel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     * })
     */
    private $channel;

    /**
     * @var \App\Entity\Sales\Tag
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Tag")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     * })
     */
    private $tag;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Sales\Contact", mappedBy="workarea")
     */
    private $contact;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contact = new ArrayCollection();
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
     * @return Workarea
     */
    public function setId(int $id): Workarea
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param null|string $token
     * @return Workarea
     */
    public function setToken(?string $token): Workarea
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getProcessedAt(): ?\DateTime
    {
        return $this->processedAt;
    }

    /**
     * @param \DateTime|null $processedAt
     * @return Workarea
     */
    public function setProcessedAt(?\DateTime $processedAt): Workarea
    {
        $this->processedAt = $processedAt;
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
     * @return Workarea
     */
    public function setCreatedAt(?\DateTime $createdAt): Workarea
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
     * @return Workarea
     */
    public function setChannel(Channel $channel): Workarea
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
     * @return Workarea
     */
    public function setTag(Tag $tag): Workarea
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getContact(): Collection
    {
        return $this->contact;
    }

    /**
     * @param Collection $contact
     * @return Workarea
     */
    public function setContact(Collection $contact): Workarea
    {
        $this->contact = $contact;
        return $this;
    }




}
