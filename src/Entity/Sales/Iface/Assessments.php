<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/18/18
 * Time: 5:32 PM
 */

namespace App\Entity\Sales\Iface;


use App\Entity\Sales\Workarea;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\InventoryRepository;
use App\Repository\Sales\PricingRepository;

class Assessments
{
    /**
     * @var FormRepository
     */
    private $formRepository;
    /**
     * @var Workarea
     */
    private $workarea;
    /**
     * @var Tag
     */
    private $playerTag;
    /**
     * @var PricingRepository
     */
    private $pricingRepository;
    /**
     * @var InventoryRepository
     */
    private $inventoryRepository;
    /**
     * @var Tag
     */
    private $assessmentTag;




    /**
     * Assessments constructor.
     * @param Workarea $workarea
     * @param Tag $playerTag
     * @param Tag $assessmentTag
     * @param FormRepository $formRepository
     * @param PricingRepository $pricingRepository
     * @param InventoryRepository $inventoryRepository
     */
    public function __construct(Workarea $workarea,
                                Tag $playerTag,
                                Tag $assessmentTag,
                                FormRepository $formRepository,
                                PricingRepository $pricingRepository,
                                InventoryRepository $inventoryRepository)
    {
        $this->formRepository = $formRepository;
        $this->workarea = $workarea;
        $this->playerTag = $playerTag;
        $this->pricingRepository = $pricingRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->assessmentTag = $assessmentTag;
    }

    public function addToDb($playerId)
    {

    }

    public function readFromDb()
    {

    }

    public function deleteFromDb(int $playerId)
    {

    }

    public function get()
    {

    }

}