<?php

namespace App\Entity\Sales;

use Doctrine\ORM\Mapping as ORM;

/**
 * Channel
 *
 * @ORM\Table(name="channel")
 * @ORM\Entity(repositoryClass="App\Repository\Sales\ChannelRepository")
 */
class Channel
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
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
     * @var string|null
     *
     * @ORM\Column(name="slug", type="string", length=45, nullable=true)
     */
    private $slug;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="online_at", type="datetime", nullable=true)
     */
    private $onlineAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="offline_at", type="datetime", nullable=true)
     */
    private $offlineAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var array
     *
     * @ORM\Column(name="heading", type="json", nullable=false)
     */
    private $heading;

    /**
     * @var string|null
     *
     * @ORM\Column(name="logo", type="blob", length=65535, nullable=false)
     */
    private $logo;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

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
     * @return Channel
     */
    public function setName(string $name): Channel
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param null|string $slug
     * @return Channel
     */
    public function setSlug(?string $slug): Channel
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getOnlineAt(): ?\DateTime
    {
        return $this->onlineAt;
    }

    /**
     * @param \DateTime|null $onlineAt
     * @return Channel
     */
    public function setOnlineAt(?\DateTime $onlineAt): Channel
    {
        $this->onlineAt = $onlineAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getOfflineAt(): ?\DateTime
    {
        return $this->offlineAt;
    }

    /**
     * @param \DateTime|null $offlineAt
     * @return Channel
     */
    public function setOfflineAt(?\DateTime $offlineAt): Channel
    {
        $this->offlineAt = $offlineAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Channel
     */
    public function setCreatedAt(\DateTime $createdAt): Channel
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * @param array $heading
     * @return Channel
     */
    public function setHeading(array $heading): Channel
    {
        $this->heading = $heading;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * @param null|string $logo
     * @return Channel
     */
    public function setLogo(?string $logo): Channel
    {
        $this->logo = $logo;
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
     * @return Channel
     */
    public function setUpdatedAt(?\DateTime $updatedAt): Channel
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function __toString()
    {
        return json_encode(['id'=>$this->id, 'name'=>$this->name]);
    }
}
