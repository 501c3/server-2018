<?php

namespace App\Entity\Sales;

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
     * @var \Doctrine\Common\Collections\Collection
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
        $this->workarea = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
