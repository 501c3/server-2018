<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/23/18
 * Time: 9:05 AM
 */

namespace App\Tests\Doctrine\Iface;


use App\Doctrine\Iface\Classify;
use App\Entity\Competition\Competition;
use App\Entity\Competition\Event;
use App\Entity\Competition\Iface;
use App\Entity\Competition\Model;
use App\Entity\Competition\Player;
use App\Entity\Models\Value;
use App\Entity\Sales\Form;
use App\Entity\Sales\Iface\Participant;
use App\Entity\Sales\Iface\Player as IfacePlayer;
use App\Entity\Sales\Iface\Qualification;
use App\Entity\Sales\Tag;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Sales\Iface\PlayerRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class PlayerPoolGeneratorTest extends KernelTestCase
{
    /** @var ParticipantPoolGenerator */
    private static $participantPool;

    /** @var PlayerPoolGenerator */
    private  static $playerPoolGenerator;

    /** @var PlayerRepository */
    private static $ifacePlayerRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param $dataFile
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function initializeDatabase(EntityManagerInterface $entityManager, $dataFile)
    {
        $purger = new ORMPurger($entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $conn = $purger->getObjectManager()->getConnection();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
        $sql = file_get_contents( __DIR__ . '/../../Scripts/SQL/' .$dataFile );
        $conn->query( $sql );
    }

    /**
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\ParticipantCheckException
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function setUpBeforeClass()
    {
        (new Dotenv())->load( __DIR__ . '/../../../.env' );
        $kernel = self::bootKernel();
        $entityManagerModels = $kernel->getContainer()->get( 'doctrine.orm.models_entity_manager' );
        $entityManagerCompetition = $kernel->getContainer()->get('doctrine.orm.competition_entity_manager');
        $entityManagerSales = $kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        self::initializeDatabase($entityManagerModels,'models.sql');
        self::initializeDatabase($entityManagerCompetition,'competition-interface.sql');
        self::initializeDatabase($entityManagerSales,'sales-channel.sql');
        /** @var CompetitionRepository $competitionRepository */
        $competitionRepository=$entityManagerCompetition->getRepository(Competition::class);
        $modelRepository=$entityManagerCompetition->getRepository(Model::class);
        $ifaceRepository=$entityManagerCompetition->getRepository(Iface::class);
        $playerRepository = $entityManagerCompetition->getRepository( Player::class);
        $eventRepository = $entityManagerCompetition->getRepository(Event::class);
        $valueRepository=$entityManagerModels->getRepository(Value::class);
        $formRepository=$entityManagerSales->getRepository(Form::class);
        $tagRepository = $entityManagerSales->getRepository(Tag::class);

        /** @var Competition $competition */
        $participantPoolGenerator = new ParticipantPoolGenerator($competitionRepository,
                                                                  $modelRepository,
                                                                  $ifaceRepository,
                                                                  $valueRepository,
                                                                  $formRepository,
                                                                  $tagRepository);

        self::$ifacePlayerRepository = new PlayerRepository($valueRepository,
                                                            $modelRepository,
                                                            $tagRepository,
                                                            $formRepository,
                                                            $competitionRepository,
                                                            $ifaceRepository,
                                                            $playerRepository,
                                                            $eventRepository);


        $fileLocation = realpath( __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/participant-pool.yml' );
        $yamlText =  file_get_contents($fileLocation);
        $participantPool = $participantPoolGenerator->parse($yamlText);
        $playerPoolGenerator = new PlayerPoolGenerator($participantPoolGenerator);
        $playerPoolGenerator->setParticipantPool($participantPool);
        self::$playerPoolGenerator=$playerPoolGenerator;
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage no competition" at row:4, col:1 expected "competition".
     * @expectedExceptionCode 6002
     */
    public function test6002ExceptionCompetition()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/base-6002-exception-competition.yml' );
        self::$playerPool->parse($yamlText);
    }


    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Competition" at row:4, col:14 does not exist.
     * @expectedExceptionCode 6004
     */


    /**
     * @throws \App\Exceptions\GeneralException
     */
    public function testCouplesSolo()
    {
        $yamlText = file_get_contents(__DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/player-pool.yml' );
        $results=self::$playerPool->parse($yamlText);
        $this->assertArrayHasKey('couples',$results);
        $this->assertArrayHasKey('solos',$results);
        foreach($results['couples'] as $couple) {
            $this->assertArrayHasKey('leader',$couple);
            $this->assertArrayHasKey('follower',$couple);
            $this->assertArrayHasKey('expected',$couple);
        }
        foreach($results['solos'] as $solo){
            $this->assertArrayHasKey('participant',$solo);
            $this->assertArrayHasKey('expected',$solo);
        }
    }

    private function expectedToJson($expected) {
        $result = [];
        /**
         * @var string $domain
         * @var  Value $value
         */
        foreach($expected as $domain=>$value){
            $result[$domain]=$value->getName();
        }
        return json_encode($result);
    }

    /**
     * @throws \App\Doctrine\Iface\ClassifyException
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testCouplesSoloClassification()
    {
        $yamlText = file_get_contents(__DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/player-pool.yml' );
        $results=self::$playerPoolGenerator->parse($yamlText);
        foreach($results['couples'] as $couple) {
            /** @var Participant $leader */
            $leader = $couple['leader'];
            /** @var Participant $follower */
            $follower = $couple['follower'];
            $expected = $couple['expected'];

//            $message = sprintf("Expected does not match actual for %s & %s.\nExpected: %s\n Actual: %s\n",
//                                $leader->getName(),$follower->getName(),$expectedJson,$actualJson);
//            $this->assertJsonStringEqualsJsonString($expectedJson,$actualJson,$message);
        }
        foreach($results['solos'] as $solo){
            /** @var Participant $participant */
            $participant = $solo['participant'];
            $expected = $solo['expected'];
            /** @var Value $expectedValue */
            $expectedValue = $expected['genre'];


            /** @var Qualification $playerQualification */
//            $playerQualification=$player->getQualification($expectedValue->getName())
//                                        ->toArray(Qualification::DOMAIN_NAME_TO_VALUE_NAME);
//            /** @var Json $actualJson */
//            $actualJson = json_encode($playerQualification);
//            $expectedJson=$this->expectedToJson($expected);
//            $message = sprintf("Expected does not match actual %s.\nExpected: %s\nActual: %s\n",
//                                $participant->getName(), $expectedJson, $actualJson);
//            $this->assertJsonStringEqualsJsonString($expectedJson,$actualJson,$message);
        }
    }
}