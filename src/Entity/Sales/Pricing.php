<?php

namespace App\Entity\Sales;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pricing
 *
 * @ORM\Table(name="pricing", indexes={@ORM\Index(name="idx_start_at", columns={"start_at"}), @ORM\Index(name="fk_pricing_inventory1_idx", columns={"inventory_id"}), @ORM\Index(name="fk_pricing_channel1_idx", columns={"channel_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\PricingRepository")
 */
class Pricing
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_at", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $startAt;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=7, scale=2, nullable=false)
     */
    private $price;


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
     * @var \App\Entity\Sales\Inventory
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="App\Entity\Sales\Inventory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="inventory_id", referencedColumnName="id")
     * })
     */
    private $inventory;

    /**
     * @return \DateTime
     */
    public function getStartAt(): \DateTime
    {
        return $this->startAt;
    }

    /**
     * @param  $startAt
     * @return Pricing
     */
    public function setStartAt(\DateTime $startAt): Pricing
    {
        $this->startAt = $startAt->format('Y-m-d');
        return $this;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @param string $price
     * @return Pricing
     */
    public function setPrice(string $price): Pricing
    {
        $this->price = $price;
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
     * @return Pricing
     */
    public function setChannel(Channel $channel): Pricing
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return Inventory
     */
    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    /**
     * @param Inventory $inventory
     * @return Pricing
     */
    public function setInventory(Inventory $inventory): Pricing
    {
        $this->inventory = $inventory;
        return $this;
    }

    public function __toString()
    {

    }


}
