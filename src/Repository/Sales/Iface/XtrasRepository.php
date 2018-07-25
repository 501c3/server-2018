<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 7/16/18
 * Time: 10:54 AM
 */

namespace App\Repository\Sales\Iface;


use App\Entity\Sales\Form;
use App\Entity\Sales\Iface\Xtras;
use App\Entity\Sales\Inventory;
use App\Entity\Sales\Pricing;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\InventoryRepository;
use App\Repository\Sales\PricingRepository;
use App\Repository\Sales\TagRepository;

class XtrasRepository
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
     * @var SummaryRepository
     */
    private $summaryRepository;

    public function __construct(ChannelRepository $channelRepository,
                                FormRepository $formRepository,
                                TagRepository $tagRepository,
                                InventoryRepository $inventoryRepository,
                                PricingRepository $pricingRepository,
                                SummaryRepository $summaryRepository)
    {

        $this->channelRepository = $channelRepository;
        $this->formRepository = $formRepository;
        $this->tagRepository = $tagRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->pricingRepository = $pricingRepository;
        $this->summaryRepository = $summaryRepository;
    }

    /**
     * @param Workarea $workarea
     * @return Xtras
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fetch(Workarea $workarea):Xtras
    {
        $tag=$this->tagRepository->fetch('extra');
        /** @var Form $form */
        $xtras = new Xtras('USD');
        $xtras->setWorkarea($workarea);
        $form=$this->formRepository->findOneBy(['tag'=>$tag, 'workarea'=>$workarea]);
        if(is_null($form)){
            $channel=$workarea->getChannel();
            $inventoryList = $this->inventoryRepository->fetchInventory($tag);
            $priceList = $this->pricingRepository->fetchCurrentPricing($channel,
                                                                    $inventoryList,
                                                                    new \DateTime('now'));
            /** @var Inventory $inventory*/
            foreach($inventoryList as $inventory) {
                $id = $inventory->getId();
                $description = $inventory->getName();
                $price = $priceList[$id];
                $xtras->setInventory($id, $description, floatval($price));
            }
            return $xtras;
        }
        $content = $form->getContent();
        $xtras->init($content);
        $xtras->setId($form->getId());

        return $xtras;
    }

    /**
     * @param Xtras $xtras
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Xtras $xtras)
    {
        $tag=$this->tagRepository->fetch('extra');
        $em=$this->formRepository->getEntityManager();
        if($xtras->hasId()) {
            /** @var Form $form */
            $form=$this->formRepository->find($xtras->getId());
            $form->setContent($xtras->toArray());
        } else {
            $form = new Form();
            $form->setWorkarea($xtras->getWorkarea())
                ->setTag($tag)
                ->setContent($xtras->toArray());
            $em->persist($form);
        }
        $em->flush();
    }
}