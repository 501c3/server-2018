<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/18/18
 * Time: 11:19 AM
 */

namespace App\Doctrine\Iface;


use App\Entity\Sales\Workarea;
use App\Repository\Competition\ModelRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\InventoryRepository;
use App\Repository\Sales\PricingRepository;

class Assess
{
    /**
     * @var InventoryRepository
     */
    private $inventoryRepository;
    /**
     * @var PricingRepository
     */
    private $pricingRepository;
    /**
     * @var ModelRepository
     */
    private $modelRepository;
    /**
     * @var Tag
     */
    private $playerTag;
    /**
     * @var Workarea
     */
    private $workarea;
    /**
     * @var FormRepository
     */
    private $formRepository;

    public function __construct(
        Tag $playerTag,
        Workarea $workarea,
        FormRepository $formRepository,
        InventoryRepository $inventoryRepository,
        PricingRepository $pricingRepository,
        ModelRepository $modelRepository
    )
    {
        $this->formRepository = $formRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->pricingRepository = $pricingRepository;
        $this->modelRepository = $modelRepository;
        $this->playerTag = $playerTag;
        $this->workarea = $workarea;
    }



}