<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 7/17/18
 * Time: 9:51 PM
 */

namespace App\Repository\Sales\Iface;


use App\Entity\Sales\Iface\Summary;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\InventoryRepository;
use App\Repository\Sales\PricingRepository;
use App\Repository\Sales\TagRepository;

class SummaryRepository
{


    /**
     * @var ChannelRepository
     */
    private $channelRepository;
    /**
     * @var FormRepository
     */
    private $formRepository;
    /**
     * @var TagRepository
     */
    private $tagRepository;
    /**
     * @var InventoryRepository
     */
    private $inventoryRepository;
    /**
     * @var PricingRepository
     */
    private $pricingRepository;

    public function __construct(ChannelRepository $channelRepository,
                                FormRepository $formRepository,
                                TagRepository $tagRepository,
                                InventoryRepository $inventoryRepository,
                                PricingRepository $pricingRepository)
    {

        $this->channelRepository = $channelRepository;
        $this->formRepository = $formRepository;
        $this->tagRepository = $tagRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->pricingRepository = $pricingRepository;
    }

    private function rebuildSummary($data)
    {
        $summary = new Summary('USD');
        $participantIds=$summary->init($data);
        $tag = $this->tagRepository->findOneBy(['name'=>'participant']);
        foreach($participantIds as $id)
        {
            $participant = $this->rebuildParticipant($id);

        }


    }


    public function read(Workarea $workarea): Summary
    {
        $tag = $this->tagRepository->fetch('summary');
        $form = $this->formRepository->findOneBy(['tag'=>$tag,'workarea'=>$workarea]);
        $data = $form?$form->getContent():null;
        $summary = $data?$this->rebuildSummary($data):new Summary('USD');
        return $summary;
    }



    public function save(Summary $summary)
    {
        $tag = $this->tagRepository->fetch('summary');
        $form = $this->formRepository->findOneBy(['tag'=>$tag,'workarea'=>$workarea]);

    }



}