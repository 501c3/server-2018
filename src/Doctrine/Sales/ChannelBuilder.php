<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/24/18
 * Time: 2:14 PM
 */

namespace App\Doctrine\Sales;


use App\Doctrine\Builder;
use App\Entity\Sales\Inventory;
use App\Entity\Sales\Parameters;
use App\Entity\Sales\Pricing;
use App\Entity\Sales\Processor;
use App\Entity\Sales\Settings;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Channel;
use App\Exceptions\GeneralException;
use App\Exceptions\MissingException;
use App\Exceptions\SalesExceptionCode;
use App\Repository\Configuration\SalesRepository;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\InventoryRepository;
use App\Repository\Sales\ParametersRepository;
use App\Repository\Sales\PricingRepository;
use App\Repository\Sales\ProcessorRepository;
use App\Repository\Sales\SettingsRepository;
use App\Repository\Sales\TagRepository;
use App\Subscriber\Status;
use App\Utils\YamlPosition;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface;


class ChannelBuilder extends Builder
{
    const EXPECTED_KEYS=['channel','competition','logo','venue','city','state','date','monitor','inventory','processor'];
    const INVALID_DATE="is invalid date.";
    const INVENTORY_TAGS=['participant','extra','discount','penalty'];
    const MONITOR='monitor';
    const SUPPORTED_PROCESSORS=['paypal','braintree'];
    /**
     * @var ChannelRepository
     */
    private $channelRepository;
    /**
     * @var InventoryRepository
     */
    private $inventoryRepository;
    /**
     * @var PricingRepository
     */
    private $pricingRepository;
    /**
     * @var ParametersRepository
     */
    private $parametersRepository;
    /**
     * @var TagRepository
     */
    private $tagRepository;
    /*
     * @var TraceableEventDispatcherInterface
     */
    //private $dispatcher;
    /**
     * @var SalesRepository
     */
    private $salesRepository;
    /**
     * @var ProcessorRepository
     */
    private $processorRepository;
    /**
     * @var SettingsRepository
     */
    private $settingsRepository;
    /**
     * @var TraceableEventDispatcherInterface
     */
    private $dispatcher;

    /**
     * ChannelBuilder constructor.
     * @param ChannelRepository $channelRepository
     * @param InventoryRepository $inventoryRepository
     * @param PricingRepository $pricingRepository
     * @param ParametersRepository $parametersRepository
     * @param TagRepository $tagRepository
     * @param ProcessorRepository $processorRepository
     * @param SettingsRepository $settingsRepository
     * @param SalesRepository $salesRepository
     * @param TraceableEventDispatcherInterface|null $dispatcher
     *
     * throws \Exception
     */
    public function __construct(
        ChannelRepository $channelRepository,
        InventoryRepository $inventoryRepository,
        PricingRepository $pricingRepository,
        ParametersRepository $parametersRepository,
        TagRepository $tagRepository,
        ProcessorRepository $processorRepository,
        SettingsRepository $settingsRepository,
        SalesRepository $salesRepository,
        TraceableEventDispatcherInterface $dispatcher=null)
    {
        $this->channelRepository = $channelRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->pricingRepository = $pricingRepository;
        $this->parametersRepository = $parametersRepository;
        $this->tagRepository = $tagRepository;
        $this->processorRepository = $processorRepository;
        $this->settingsRepository = $settingsRepository;
        $this->salesRepository = $salesRepository;
        $this->dispatcher = $dispatcher;
    }


    /**
     * @param string $yamlTxt
     * @return bool
     * @throws GeneralException
     * @throws MissingException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function build(string $yamlTxt)
    {
        parent::setDispatcher($this->dispatcher);
        $result = YamlPosition::parse($yamlTxt);
        $lineCount=YamlPosition::getLineCount();
        $this->sendStatus(Status::COMMENCE, $lineCount);
        $data=[];$position=[];$dataKeys=[];$positionKeys=[];
        list($tmpData, $tmpPosition, $tmpDataKey, $tmpPositionKey)
            =$this->current($result['data'], $result['position']);
        while($tmpData && $tmpPosition){
            $data[$tmpDataKey]=$tmpData;
            $position[$tmpDataKey]=$tmpPosition;
            $dataKeys[]=$tmpDataKey;
            $positionKeys[]=$tmpPositionKey;
            list($tmpData, $tmpPosition, $tmpDataKey, $tmpPositionKey)
                = $this->next($result['data'],$result['position']);
        }
        $this->checkTopKeysValid($dataKeys, $positionKeys);
        $this->checkTopKeysMissing($dataKeys, $positionKeys);
        $this->checkDate($data['date'],$position['date']);
        $channel=$this->buildChannel($data['channel'],
                                     $data['competition'],
                                     $data['logo'], $position['logo'],
                                     $data['venue'],
                                     $data['city'],
                                     $data['state'],
                                     $data['date']);
        $this->buildMonitor($channel, $data['monitor'], $position['monitor']);
        $this->buildInventory($channel, $data['inventory'], $position['inventory']);
        $this->buildProcessor($channel, $data['processor'], $position['processor']);
        $this->sendStatus(Status::COMPLETE,100);
        return true;
    }

    /**
     * @param array $dataKeys
     * @param array $positionKeys
     * @throws GeneralException
     */
    private function checkTopKeysValid(array $dataKeys, array $positionKeys)
    {
        $data = current($dataKeys);
        $position= current($positionKeys);
        $expectedKeys = self::EXPECTED_KEYS;
        array_push($expectedKeys,'comment');
        while($data && $position){
            if(!in_array($data,$expectedKeys)){
                $keys = join('","', self::EXPECTED_KEYS);
                throw new GeneralException($data, $position, "expected \"$keys\"",
                                        SalesExceptionCode::KEYS);
            }
            $data=next($dataKeys);
            $position=next($positionKeys);
        }
    }

    /**
     * @param $dataKeys
     * @param $positionKeys
     * @throws MissingException
     */
    private function checkTopKeysMissing($dataKeys, $positionKeys)
    {
         $missingKeys=array_diff(self::EXPECTED_KEYS, $dataKeys);
         if(count($missingKeys)){
             throw new MissingException($missingKeys,$positionKeys,SalesExceptionCode::MISSING);
         }
    }


    /**
     * @param $data
     * @param $position
     * @throws GeneralException
     */
    private function checkDate($data,$position)
    {
        list($startDate, $startDatePos, $startKey, $startKeyPosition)
            = $this->current($data,$position);

        if($startKey!='start') {
            throw new GeneralException($startKey, $startKeyPosition, "expected \"start\"",
                                        SalesExceptionCode::START);

        }
        list($finishDate, $finishDatePos, $finishKey, $finishKeyPosition)
            = $this->next($data,$position);

        if($finishKey!='finish') {
            throw new GeneralException($finishKey, $finishKeyPosition,"expected \"finish\"",
                                        SalesExceptionCode::FINISH);
        }

        $this->checkDateValid($startDate, $startDatePos);
        $this->checkDateValid($finishDate, $finishDatePos);
    }


    /**
     * @param $date
     * @param $position
     * @return bool
     * @throws GeneralException
     */
    private function checkDateValid($date, $position)
    {
        if(is_int($date)){
            $string=date('Y-m-d H:i:s.u',$date);
            $unixTime=strtotime($string);
            if($unixTime==$date){
                return true;
            }
        }elseif(is_string($date)){
            $unixTime = strtotime($date);
            $string = date('Y-m-d',$unixTime);
            if($string==$date){
                return true;
            }
        }
        throw new GeneralException($date,$position, "format not YYYY-MM-DD or invalid date",
                                    SalesExceptionCode::DATE);
    }

    /**
     * @param $name
     * @return Tag|null|object
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function fetchTag($name)
    {
        $tag=$this->tagRepository->findOneBy(['name'=>$name]);
        if(is_null($tag)){
            $tag=new Tag();
            $tag->setName($name);
            $em=$this->tagRepository->getEntityManager();
            $em->persist($tag);
            $em->flush();
        }
        return $tag;
    }


    /**
     * @param string $channelName
     * @param string $competitionName
     * @param string $logoFile
     * @param string $logoPos
     * @param string $venue
     * @param string $city
     * @param string $state
     * @param array $dateData
     * @return Channel
     * @throws GeneralException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildChannel(string $channelName,
                                  string $competitionName,
                                  string $logoFile, string $logoPos,
                                  string $venue,
                                  string $city,
                                  string $state,
                                  array $dateData) : Channel
    {
       $start=new \DateTime(gmdate('Y-m-d',$dateData['start']));
       $finish=new \DateTime(gmdate('Y-m-d',$dateData['finish']));
       $dateText=$start->format('D M d, Y');
       if($finish->diff($start)->days!=0){
            $dateText.=' to '.$finish->format('D M d, Y');
       }


       $heading = [ 'name'=>$channelName,
                    'competition'=>$competitionName,
                    'location'=>sprintf('%s, %s, %s',$venue,$city,$state),
                    'date'=>$dateText];

       $imageFileType=strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
       try{
            $blob=base64_encode(file_get_contents($logoFile));
       } catch (\Exception $e) {
            throw new GeneralException($logoFile,$logoPos,"not found", SalesExceptionCode::LOGO);
       }
       $image='data:image/'.$imageFileType.';base64,'.$blob;
       $channel = new Channel();
       $channel->setName($channelName)
                ->setSlug($this->slug($channelName))
                ->setHeading($heading)
                ->setLogo($image)
                ->setCreatedAt(new \DateTime('now'));
       $em=$this->channelRepository->getEntityManager();
       $em->clear();
       $em->persist($channel);
       $em->flush();
       return $channel;
    }

    /**
     * @param Channel $channel
     * @param $data
     * @param $position
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildMonitor(Channel $channel, $data, $position)
    {
        $tag=$this->fetchTag('monitor');
        list($email,$emailPosition,$name,)=$this->current($data,$position);
        while($email && $emailPosition){
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new GeneralException($email,$emailPosition, "invalid email format",
                    SalesExceptionCode::EMAIL);
            }
            $this->addParameter($channel, $tag, $name, $email);
            list($email, $emailPosition,$name,)=$this->next($data,$position);
        }
    }

    /**
     * @param Channel $channel
     * @param Tag $tag
     * @param string $key
     * @param string $value
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function addParameter(Channel $channel, Tag $tag, string $key, string $value){
        $em=$this->parametersRepository->getEntityManager();
        $parameter=new Parameters();
        $parameter->setChannel($channel)
                ->setTag($tag)
                ->setKey($key)
                ->setValue($value)
                ->setCreatedAt(new \DateTime('now'));
        $em->persist($parameter);
        $em->flush($parameter);
    }

    /**
     * @param Channel $channel
     * @param $data
     * @param $position
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildInventory(Channel $channel, $data, $position)
    {
        list($products, $productsPosition, $tagKey, $tagPositionKey) = $this->current($data, $position);
        if($tagKey!=='participant'){
            throw new GeneralException($tagKey, $tagPositionKey ,"expected \"participant\"",
                                        SalesExceptionCode::PARTICIPANT);
        }
        while($products && $productsPosition){
            if(!in_array($tagKey, self::INVENTORY_TAGS)){
                $message = sprintf('expected "%s"', join('","',self::INVENTORY_TAGS));
                throw new GeneralException($tagKey,$tagPositionKey,$message,
                                        SalesExceptionCode::TAGS);
            }
            $tag=$this->fetchTag($tagKey);
            $this->buildInventorySection($channel, $tag, $products, $productsPosition);
            list($products, $productsPosition, $tagKey, $tagPositionKey) = $this->next($data, $position);
        }
    }

    /**
     * @param Channel $channel
     * @param Tag $tag
     * @param array $products
     * @param array $productsPosition
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildInventorySection(Channel $channel, Tag $tag, array $products, array $productsPosition)
    {
        $em=$this->inventoryRepository->getEntityManager();
        list($pricing, $pricingPosition, $productName, )=$this->current($products, $productsPosition);
        while($pricing && $pricingPosition){
            $inventory = new Inventory();
            $inventory->setTag($tag)
                        ->setName($productName)
                        ->setCreatedAt(new \DateTime('now'));
            $em->persist($inventory);
            $em->flush();
            $this->buildDatePricing($channel, $inventory, $pricing, $pricingPosition);
            list($pricing, $pricingPosition, $productName, )=$this->next($products, $productsPosition);
        }
    }

    /**
     * @param Channel $channel
     * @param Inventory $inventory
     * @param array $pricing
     * @param array $pricingPosition
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildDatePricing(Channel $channel, Inventory $inventory,
                                      array $pricing, array $pricingPosition)
    {

        list($price, $pricePosition,$date,$datePosition)=$this->current($pricing, $pricingPosition);
        while($price && $pricePosition){
            $this->checkDateValid($date,$datePosition);
            $this->buildPricing($channel, $inventory, $date, $price, $pricePosition);
            list($price, $pricePosition,$date,$datePosition)=$this->next($pricing, $pricingPosition);
        }
    }

    /**
     * @param Channel $channel
     * @param Inventory $inventory
     * @param $date
     * @param $price
     * @param $pricePosition
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildPricing(Channel $channel, Inventory $inventory, $date, $price, $pricePosition)
    {
        if (!is_double( $price )) {
            throw new GeneralException( $price, $pricePosition, "expect double.",
                SalesExceptionCode::DOUBLE );
        }
        $pricing = new Pricing();
        $pricing->setChannel( $channel )
            ->setInventory( $inventory )
            ->setPrice( $price )
            ->setStartAt( new \DateTime( $date ) );
        $em = $this->pricingRepository->getEntityManager();
        $em->persist( $pricing );
        $em->flush();
    }

    /**
     * @param Channel $channel
     * @param $data
     * @param $position
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildProcessor(Channel $channel, $data, $position)
    {
        list($settingsPart, $positionPart, $processorKey, $processorKeyPosition)
            =$this->current($data, $position);
        while($settingsPart && $positionPart){
            if(!in_array(strtolower($processorKey), self::SUPPORTED_PROCESSORS)){
                throw new GeneralException($processorKey, $processorKeyPosition, "unsupported processor.",
                                            SalesExceptionCode::PROCESSOR);
            }
            $processor=new Processor();
            $processor->setName($processorKey);
            $em=$this->processorRepository->getEntityManager();
            $em->persist($processor);
            $em->flush();
            $this->saveProcessorParameters($channel, $processor, $settingsPart, $positionPart);
            list($settingsPart, $positionPart, $processorKey, $processorKeyPosition)
                =$this->next($data, $position);
        }
    }


    /**
     * @param Channel $channel
     * @param Processor $processor
     * @param $data
     * @param $position
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function saveProcessorParameters(Channel $channel, Processor $processor, $data, $position)
    {
        $keyList=['prod','test'];
        $keyListStr=join('","',$keyList);
        list($settings, $settingsPos, $key, $keyPos)=$this->current($data,$position);
        while($settings && $settingsPos){
           if(!in_array($key,$keyList)){
               $message = sprintf('expected "%s"',$keyListStr);
               throw new GeneralException($key, $keyPos, $message, SalesExceptionCode::PROD_TEST);
           }
           $tag=$this->fetchTag($key);
           /** @var string */
           $parametersJson=json_encode($settings);
           $settings=new Settings();
           /** @var Settings $settings */
           $settings->setChannel($channel)
                    ->setProcessor($processor)
                    ->setTag($tag)
                    ->setData($parametersJson);
           $em=$this->settingsRepository->getEntityManager();
           $em->persist($settings);
           $em->flush();
           list($settings, $settingsPos, $key, $keyPos)=$this->next($data, $position);
        }
    }


    /**
     * @param $string
     * @return null|string|string[]
     */
    private function slug(string $string)
    {
        return preg_replace('/\s+/','-',strtolower($string));
    }

}