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


use App\Entity\Sales\Form;
use App\Entity\Sales\Iface\Summary;
use App\Entity\Sales\Pricing;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\InventoryRepository;
use App\Repository\Sales\PricingRepository;
use App\Repository\Sales\ReceiptsRepository;
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
    /**
     * @var ReceiptsRepository
     */
    private $receiptsRepository;


    public function __construct(ChannelRepository $channelRepository,
                                FormRepository $formRepository,
                                TagRepository $tagRepository,
                                InventoryRepository $inventoryRepository,
                                PricingRepository $pricingRepository,
                                ReceiptsRepository $receiptsRepository)
    {
        $this->channelRepository = $channelRepository;
        $this->formRepository = $formRepository;
        $this->tagRepository = $tagRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->pricingRepository = $pricingRepository;
        $this->receiptsRepository = $receiptsRepository;
    }

    /**
     * @param Workarea $workarea
     * @return Summary
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function read(Workarea $workarea): Summary
    {
        $tag = $this->tagRepository->fetch('summary');
        $form = $this->formRepository->findOneBy(['tag'=>$tag,'workarea'=>$workarea]);
        $data = $form?$form->getContent():null;
        $summary = $this->buildSummary($workarea,$data);
        return $summary;
    }


    /**
     * @param Workarea $workarea
     * @param array|null $data
     * @return Summary
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function buildSummary(Workarea $workarea,array $data=null)
    {
        $summary=new Summary('USD');
        $inventoryList = $this->inventoryRepository->findAll();
        /** @var Pricing $pricing */
        $pricingList=$this->pricingRepository
                            ->fetchCurrentPricing($workarea->getChannel(),
                                                  $inventoryList,
                                                  new \DateTime('now'));

        $summary->setInventory($pricingList);
        if($data) {
            $summary->init($data);
        }
        return $summary;
    }

    /**
     * @param Workarea $workarea
     * @param Summary $summary
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Workarea $workarea, Summary $summary)
    {
        $tag = $this->tagRepository->fetch('summary');
        $form = $this->formRepository->findOneBy(['tag'=>$tag,'workarea'=>$workarea]);
        $data = $summary->toArray();
        $em = $this->formRepository->getEntityManager();
        if(is_null($form)){
            $form = new Form();
            $form->setTag($tag)
                ->setWorkarea($workarea);
            $em->persist($form);
        }
        $form->setContent($data);
        $em->flush();
    }
}