<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/20/18
 * Time: 11:01 PM
 */

namespace App\Tests\Doctrine\Iface;


use App\Doctrine\Iface\PlayerEvents;
use App\Entity\Competition\Competition;
use App\Entity\Competition\Event;
use App\Entity\Competition\Iface;
use App\Entity\Competition\Model as ModelCompetition;
use App\Entity\Competition\Player;
use App\Entity\Models\Model;
use App\Entity\Models\Value;
use App\Exceptions\GeneralException;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class CompetitionSalesParserTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private static $entityManagerSales;

    /** @var EntityManagerInterface */
    private static $entityManagerCompetition;

    /** @var EntityManagerInterface */
    private static $entityManagerModels;

    /** @var CompetitionSalesParser*/
    private $competitionSalesParser;

    /** @var PlayerEvents */
    private $playerEvents;

    /**
     * @param EntityManagerInterface $entityManager
     * @throws \Doctrine\DBAL\DBALException
     */
    private function purgeDb(EntityManagerInterface $entityManager)
    {
        $purger = new ORMPurger($entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $conn = $purger->getObjectManager()->getConnection();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
    }






   public static function setUpBeforeClass()
   {
       (new Dotenv())->load( __DIR__ . '/../../../.env' );
       $kernel = self::bootKernel();
       self::$entityManagerSales = $kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
       self::$entityManagerCompetition = $kernel->getContainer()->get('doctrine.orm.competition_entity_manager');
       self::$entityManagerModels = $kernel->getContainer()->get('doctrine.orm.models_entity_manager');
   }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
   public function setUp()
   {
        $this->purgeDb(self::$entityManagerCompetition);
        $this->purgeDb(self::$entityManagerSales);
        $this->purgeDb(self::$entityManagerModels);
        self::initializeDatabase(self::$entityManagerModels, 'models.sql');
        self::initializeDatabase(self::$entityManagerCompetition, 'competition-interface.sql');
        $competitionRepository = self::$entityManagerCompetition->getRepository(Competition::class);
        $ifaceRepository = self::$entityManagerCompetition->getRepository(Iface::class);
        $modelCompetitionRepository = self::$entityManagerCompetition->getRepository(ModelCompetition::class);
        $playerRepository = self::$entityManagerCompetition->getRepository(Player::class);
        $eventRepository = self::$entityManagerCompetition->getRepository(Event::class);
        $this->playerEvents = new PlayerEvents($competitionRepository,
                                                $ifaceRepository,
                                                $modelCompetitionRepository,
                                                $playerRepository,
                                                $eventRepository);
        $valueRepository = self::$entityManagerModels->getRepository(Value::class);
        $this->competitionSalesParser = new CompetitionSalesParser( $competitionRepository,
                                                                    $modelCompetitionRepository,
                                                                    $valueRepository);
   }


   /**
    * @expectedException \App\Exceptions\GeneralException
    * @expectedExceptionMessage "no competition" at row:4, col:1 expected "competition".
    * @expectedExceptionCode 6002
    */
   public function test6002ExceptionCompetition()
   {
       $yamlText = file_get_contents(
           __DIR__ . '/../../Scripts/Yaml/Iface/base-6002-exception-competition.yml' );
       $this->competitionSalesParser->parse( $yamlText );
       $this->assertTrue(true);
   }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Competition" at row:4, col:14 does not exist.
     * @expectedExceptionCode 6004
     */
   public function test6004ExceptionInvalidCompetition()
   {
       $yamlText = file_get_contents(
           __DIR__ . '/../../Scripts/Yaml/Iface/base-6004-exception-invalid-competition.yml' );
       $this->competitionSalesParser->parse( $yamlText );
   }


      /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no models" at row:5, col:1 expected "models".
     * @expectedExceptionCode 6006
     */
    public function test6006ExceptionModels()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/base-6006-exception-models.yml' );
        $this->competitionSalesParser->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Model" at row:5, col:37 is invalid.
     * @expectedExceptionCode 6008
     */
   public function test6008ExceptionInvalidModel()
   {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/base-6008-exception-invalid-model.yml' );
        $this->competitionSalesParser->parse($yamlText);
   }

   public function test6100ExceptionSubmissions()
   {

   }

   public function testSetupSuccessful()
   {
       $yamlText = file_get_contents(
           __DIR__ . '/../../Scripts/Yaml/Iface/participants-teams-amateur.yml' );
       $this->competitionSalesParser->parse( $yamlText );
       $this->assertTrue(true);
   }
}