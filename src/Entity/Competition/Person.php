<?php

namespace App\Entity\Competition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 *
 * @ORM\Table(name="person")
 * @ORM\Entity(repositoryClass="App\Repository\Competition\PersonRepository")
 */
class Person
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
     * @ORM\Column(name="first", type="string", length=40, nullable=false)
     */
    private $first;

    /**
     * @var string
     *
     * @ORM\Column(name="last", type="string", length=40, nullable=false)
     */
    private $last;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sex", type="string", length=1, nullable=true, options={"fixed"=true})
     */
    private $sex;

    /**
     * @var int|null
     *
     * @ORM\Column(name="age", type="smallint", nullable=true)
     */
    private $age;

    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=true)
     */
    private $status;


    /**
     * @var string|null
     *
     * @ORM\Column(name="phone", type="string", length=12, nullable=true)
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=80, nullable=true)
     */
    private $email;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Competition\Number", inversedBy="person")
     * @ORM\JoinTable(name="person_has_number",
     *   joinColumns={
     *     @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="number_id", referencedColumnName="id")
     *   }
     * )
     */
    private $number;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Competition\Team", mappedBy="person")
     */
    private $team;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->number = new ArrayCollection();
        $this->team = new ArrayCollection();
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
     * @return Person
     */
    public function setId(int $id): Person
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
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }


}
