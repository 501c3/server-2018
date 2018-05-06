<?php

namespace App\Entity\Sales;

use Doctrine\ORM\Mapping as ORM;

/**
 * Workarea
 *
 * @ORM\Table(name="workarea", indexes={@ORM\Index(name="fk_workarea_channel1_idx", columns={"channel_id"}), @ORM\Index(name="fk_workarea_tag1_idx", columns={"tag_id"})})
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
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Sales\Contact", mappedBy="workarea")
     */
    private $contact;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contact = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
