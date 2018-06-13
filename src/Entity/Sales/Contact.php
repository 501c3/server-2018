<?php

namespace App\Entity\Sales;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contact
 *
 * @ORM\Table(name="contact", indexes={@ORM\Index(name="idx_contact_name", columns={"last", "first"}), @ORM\Index(name="idx_email", columns={"email"}), @ORM\Index(name="idx_send_elink", columns={"last", "pin"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\ContactRepository")
 */
class Contact
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
     * @ORM\Column(name="status", type="string", length=12, nullable=true)
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=40, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="first", type="string", length=40, nullable=false)
     */
    private $first;

    /**
     * @var string|null
     *
     * @ORM\Column(name="middle", type="string", length=40, nullable=true)
     */
    private $middle;

    /**
     * @var string
     *
     * @ORM\Column(name="last", type="string", length=40, nullable=false)
     */
    private $last;

    /**
     * @var string|null
     *
     * @ORM\Column(name="suffix", type="string", length=4, nullable=true)
     */
    private $suffix;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone", type="string", length=16, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=80, nullable=false)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="city", type="string", length=16, nullable=true)
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(name="st", type="string", length=2, nullable=true)
     */
    private $st;

    /**
     * @var string|null
     *
     * @ORM\Column(name="country", type="string", length=16, nullable=true)
     */
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(name="postal", type="string", length=12, nullable=true)
     */
    private $postal;

    /**
     * @var string|null
     *
     * @ORM\Column(name="organization", type="string", length=80, nullable=true)
     */
    private $organization;

    /**
     * @var string|null
     *
     * @ORM\Column(name="elink", type="string", length=120, nullable=true)
     */
    private $elink;

    /**
     * @var int|null
     *
     * @ORM\Column(name="pin", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $pin;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Sales\Workarea", inversedBy="contact")
     * @ORM\JoinTable(name="contact_has_workarea",
     *   joinColumns={
     *     @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="workarea_id", referencedColumnName="id")
     *   }
     * )
     */
    private $workarea;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->workarea = new ArrayCollection();
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
     * @return Contact
     */
    public function setId(int $id): Contact
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param null|string $status
     * @return Contact
     */
    public function setStatus(?string $status): Contact
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     * @return Contact
     */
    public function setTitle(?string $title): Contact
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirst(): string
    {
        return $this->first;
    }

    /**
     * @param string $first
     * @return Contact
     */
    public function setFirst(string $first): Contact
    {
        $this->first = $first;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getMiddle(): ?string
    {
        return $this->middle;
    }

    /**
     * @param null|string $middle
     * @return Contact
     */
    public function setMiddle(?string $middle): Contact
    {
        $this->middle = $middle;
        return $this;
    }

    /**
     * @return string
     */
    public function getLast(): string
    {
        return $this->last;
    }

    /**
     * @param string $last
     * @return Contact
     */
    public function setLast(string $last): Contact
    {
        $this->last = $last;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @param null|string $suffix
     * @return Contact
     */
    public function setSuffix(?string $suffix): Contact
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param null|string $phone
     * @return Contact
     */
    public function setPhone(?string $phone): Contact
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Contact
     */
    public function setEmail(string $email): Contact
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param null|string $city
     * @return Contact
     */
    public function setCity(?string $city): Contact
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSt(): ?string
    {
        return $this->st;
    }

    /**
     * @param null|string $st
     * @return Contact
     */
    public function setSt(?string $st): Contact
    {
        $this->st = $st;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param null|string $country
     * @return Contact
     */
    public function setCountry(?string $country): Contact
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPostal(): ?string
    {
        return $this->postal;
    }

    /**
     * @param null|string $postal
     * @return Contact
     */
    public function setPostal(?string $postal): Contact
    {
        $this->postal = $postal;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    /**
     * @param null|string $organization
     * @return Contact
     */
    public function setOrganization(?string $organization): Contact
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getElink(): ?string
    {
        return $this->elink;
    }

    /**
     * @param null|string $elink
     * @return Contact
     */
    public function setElink(?string $elink): Contact
    {
        $this->elink = $elink;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPin(): ?int
    {
        return $this->pin;
    }

    /**
     * @param int|null $pin
     * @return Contact
     */
    public function setPin(?int $pin): Contact
    {
        $this->pin = $pin;
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
     * @return Contact
     */
    public function setCreatedAt(?\DateTime $createdAt): Contact
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getWorkarea(): Collection
    {
        return $this->workarea;
    }

    /**
     * @param Collection $workarea
     * @return Contact
     */
    public function setWorkarea(Collection $workarea): Contact
    {
        $this->workarea = $workarea;
        return $this;
    }


}
