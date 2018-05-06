<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/24/18
 * Time: 2:30 PM
 */

namespace App\Tests\Doctrine\Sales;


use App\Doctrine\Sales\ChannelBuilder;
use App\Entity\Configuration\Sales;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Inventory;
use App\Entity\Sales\Parameters;
use App\Entity\Sales\Pricing;
use App\Entity\Sales\Processor;
use App\Entity\Sales\Settings;
use App\Entity\Sales\Tag;
use App\Repository\Configuration\SalesRepository;
use App\Repository\Sales\InventoryRepository;
use App\Repository\Sales\ParametersRepository;
use App\Repository\Sales\PricingRepository;
use App\Repository\Sales\ProcessorRepository;
use App\Repository\Sales\SettingsRepository;
use App\Repository\Sales\TagRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Egulias\EmailValidator\Exception\CRLFX2;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class ChannelInventoryTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private static $entityManagerSales;

    /** @var EntityManagerInterface */
    private static $entityManagerConfiguration;

    /** @var ChannelBuilder */
    private $channelBuilder;

    public static function setUpBeforeClass()
    {
        (new Dotenv())->load(__DIR__.'/../../../.env');
        $kernel = self::bootKernel();
        self::$entityManagerSales = $kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        self::$entityManagerConfiguration = $kernel->getContainer()->get('doctrine.orm.configuration_entity_manager');
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */

    public function setUp()
    {
        $purgerSales =  new ORMPurger( self::$entityManagerSales);
        $purgerConfiguration = new ORMPurger(self::$entityManagerConfiguration);
        $purgerSales->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purgerConfiguration->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $connectionSales=$purgerSales->getObjectManager()->getConnection();
        $connectionConfiguration=$purgerConfiguration->getObjectManager()->getConnection();
        $connectionSales->query('SET FOREIGN_KEY_CHECKS=0');
        $connectionConfiguration->query('SET FOREIGN_KEY_CHECKS=0');
        $purgerSales->purge();
        $purgerConfiguration->purge();
        $connectionSales->query('SET FOREIGN_KEY_CHECKS=1');
        $connectionConfiguration->query('SET FOREIGN_KEY_CHECKS=1');
        self::$entityManagerSales->clear();
        self::$entityManagerConfiguration->clear();
        $channelRepository = self::$entityManagerSales->getRepository(Channel::class);
        /** @var InventoryRepository $inventoryRepository */
        $inventoryRepository = self::$entityManagerSales->getRepository(Inventory::class);
        /** @var PricingRepository $pricingRepository */
        $pricingRepository = self::$entityManagerSales->getRepository(Pricing::class);
        /** @var ParametersRepository $parameterRepository */
        $parameterRepository = self::$entityManagerSales->getRepository(Parameters::class);
        /** @var TagRepository $tagRepository */
        $tagRepository = self::$entityManagerSales->getRepository(Tag::class);
        /** @var ProcessorRepository $processorRepository */
        $processorRepository = self::$entityManagerSales->getRepository(Processor::class);
        /** @var SettingsRepository $settingsRepository */
        $settingsRepository=self::$entityManagerSales->getRepository(Settings::class);
        /** @var SalesRepository $salesRepository */
        $salesRepository = self::$entityManagerConfiguration->getRepository(Sales::class);

        $this->channelBuilder = new ChannelBuilder($channelRepository,
                                                    $inventoryRepository,
                                                    $pricingRepository,
                                                    $parameterRepository,
                                                    $tagRepository,
                                                    $processorRepository,
                                                    $settingsRepository,
                                                    $salesRepository);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_channel" at row:4, col:1 expected "channel","competition","logo","venue","city","state","date","monitor","inventory","processor".
     * @expectedExceptionCode  2002
     *
     * SalesExceptionCode::KEYS = 2002
     */
    public function test2002SalesExceptionKeys()
    {
        $yamlTxt=file_get_contents( __DIR__ . '/../../Scripts/Yaml/Sales/sales-2002-exception-keys.yml' );
        $this->channelBuilder->build($yamlTxt);
    }

    /**
     * @expectedException \App\Exceptions\MissingException
     * @expectedExceptionMessage Missing "venue","city","state","date" domain definitions between lines 1 and 25.
     * @expectedExceptionCode 2004
     *
     * SalesExceptionCode::MISSING = 2004
     */
    public function test2004SalesExceptionMissing()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Sales/sales-2004-exception-missing.yml');
        $this->channelBuilder->build($yamlTxt);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "/home/mgarber/Dev2018/server/badlocation/images/dancers-icon.png" at row:6, col:7 not found.
     * @expectedExceptionCode 2202
     *
     * SalesExceptionCode::LOGO = 2202
     */
    public function test2202SalesExceptionLogo()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Sales/sales-2202-exception-logo.yml');
        $this->channelBuilder->build($yamlTxt);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_start" at row:10, col:8 expected "start".
     * @expectedExceptionCode 2402
     *
     * SalesExceptionCode::START = 2402
     */

    public function test2402SalesExceptionStart()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Sales/sales-2402-exception-start.yml');
        $this->channelBuilder->build($yamlTxt);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_finish" at row:10, col:26 expected "finish".
     * @expectedExceptionCode 2404
     *
     * SalesExceptionCode::FINISH = 2404
     */
    public function  test2404SalesExceptionFinish()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Sales/sales-2404-exception-finish.yml');
        $this->channelBuilder->build($yamlTxt);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "09-15-2018" at row:10, col:15 format not YYYY-MM-DD or invalid date.
     * @expectedExceptionCode 2406
     *
     * SalesExceptionCode::DATE = 2406
     */
    public function test2406SalesExceptionDate()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Sales/sales-2406-exception-date.yml');
        $this->channelBuilder->build($yamlTxt);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "bad@invalid_email" at row:12, col:17 invalid email format.
     * @expectedExceptionCode 2502
     *
     * SalesExceptionCode::EMAIL = 2502
     */
    public function test2502SalesExceptionEmail()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Sales/sales-2502-exception-email.yml');
        $this->channelBuilder->build($yamlTxt);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_participant" at row:15, col:5 expected "participant".
     * @expectedExceptionCode 2602
     *
     * SalesExceptionCode::PARTICIPANT = 2602
     */
    public function test2602SalesExceptionParticipant()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Sales/sales-2602-exception-participant.yml');
        $this->channelBuilder->build($yamlTxt);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "2018-0-01" at row:19, col:25 format not YYYY-MM-DD or invalid date.
     * @expectedExceptionCode 2406
     *
     * SalesExceptionCode::DATE = 2406
     */
    public function test2406SalesExceptionPriceDate()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Sales/sales-2606-exception-price-date.yml');
        $this->channelBuilder->build($yamlTxt);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid_tag" at row:20, col:5 expected "participant","extra","discount","penalty".
     * @expectedExceptionCode 2604
     *
     * SalesExceptionCode::TAGS = 2604
     *
     */
    public function test2604SalesExceptionTags()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Sales/sales-2604-exception-tags.yml');
        $this->channelBuilder->build($yamlTxt);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_test" at row:30, col:9 expected "prod","test".
     * @expectedException 2702
     *
     * SalesExceptionCode::PROD_TEST=2702
     */
    public function test2702SalesExceptionProdTest()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Sales/sales-2702-exception-prod-test.yml');
        $this->channelBuilder->build($yamlTxt);
    }

    public function testSalesCorrect()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Sales/sales-correct.yml');
        $this->channelBuilder->build($yamlTxt);
        $this->assertTrue(true);
    }


}