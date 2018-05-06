<?php

namespace App\Entity\Configuration;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sales
 *
 * @ORM\Table(name="sales", indexes={@ORM\Index(name="idx_channel_name", columns={"name"}), @ORM\Index(name="idx_channel_id", columns={"channel_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Configuration\SalesRepository")
 */
class Sales
{
    /**
     * @var int
     *
     * @ORM\Column(name="channel_id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $channelId;

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


}
