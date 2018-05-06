<?php

namespace App\Entity\Sales;

use Doctrine\ORM\Mapping as ORM;

/**
 * Picture
 *
 * @ORM\Table(name="picture")
 * @ORM\Entity(repositoryClass="App\Repository\Sales\PictureRepository")
 */
class Picture
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
     * @var string
     *
     * @ORM\Column(name="data", type="blob", length=65535, nullable=false)
     */
    private $data;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Sales\Form", inversedBy="picture")
     * @ORM\JoinTable(name="picture_has_form",
     *   joinColumns={
     *     @ORM\JoinColumn(name="picture_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="form_id", referencedColumnName="id")
     *   }
     * )
     */
    private $form;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->form = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
