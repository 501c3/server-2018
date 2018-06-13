<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/4/18
 * Time: 11:14 PM
 */

namespace App\Tests\Doctrine\Iface;


use App\Entity\Competition\Competition;
use App\Entity\Competition\Event;
use App\Entity\Competition\Model;

use App\Entity\Competition\Player;
use App\Entity\Models\Value;
use App\Repository\Competition\EventRepository;
use App\Repository\Competition\PlayerRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class EventSelectTest extends KernelTestCase
{
    const PLAYER_EVENT_PROAM =
             ['Pre Bronze'=>['Pre Bronze','Intermediate Bronze','Full Bronze'],
              'Intermediate Bronze'=>['Intermediate Bronze','Full Bronze','Open Bronze'],
              'Full Bronze'=>['Full Bronze','Open Bronze','Pre Silver'],
              'Open Bronze'=>['Open Bronze','Pre Silver','Intermediate Silver'],
              'Pre Silver'=>['Pre Silver','Intermediate Silver','Full Silver'],
              'Intermediate Silver'=>['Intermediate Silver','Full Silver','Open Silver'],
              'Full Silver'=>['Full Silver','Open Silver','Pre Gold'],
              'Open Silver'=>['Open Silver','Pre Gold','Intermediate Gold'],
              'Pre Gold'=>['Pre Gold','Intermediate Gold','Full Gold'],
              'Intermediate Gold'=>['Intermediate Gold','Full Gold','Open Gold'],
              'Full Gold'=>['Full Gold','Open Gold'],
              'Open Gold'=>['Open Gold']];

    const PLAYER_EVENT_AMATEUR =
           ['Social'=>['Social'],
            'Newcomer'=>['Newcomer','Bronze','Silver-Gold','Bronze-Silver','Social'],
            'Bronze'=>['Bronze','Silver','Silver-Gold','Bronze-Silver','Silver-Gold','Social'],
            'Silver'=>['Silver','Gold','Silver-Gold','Bronze-Silver','Silver-Gold','Social'],
            'Gold'=>['Gold','Novice','Silver-Gold','Social'],
            'Novice'=>['Novice','Pre Championship','Social'],
            'Pre Championship'=>['Pre Championship','Championship','Social'],
            'Championship'=>['Championship','Social']];
    const PLAYER_EVENT_ISTD =
            ['Pre Bronze'=>['Pre Bronze'],
             'Bronze'=>['Bronze'],
             'Silver'=>['Silver'],
             'Gold'=>['Gold']];

    const PLAYER_EVENT_AGES_USA =
            ['Baby'=>['Baby','Juvenile','Youngster'],
             'Juvenile'=>['Juvenile','Preteen 1','Youngster'],
             'Preteen 1'=>['Preteen 1','Preteen 2','Youngster'],
             'Preteen 2'=>['Preteen 2','Junior 1','Youngster'],
             'Junior 1'=>['Junior 1','Junior 2'],
             'Junior 2'=>['Junior 2','Youth'],
             'Youth'=>['Youth','Adult'],
             'Adult'=>['Adult'],
             'Senior 1'=>['Senior 1','Adult'],
             'Senior 2'=>['Senior 2','Senior 1'],
             'Senior 3'=>['Senior 3','Senior 2'],
             'Senior 4'=>['Senior 4','Senior 3'],
             'Senior 5'=>['Senior 5','Senior 4'],
             'Adult Youngster'=>['Adult Youngster'],
             'Senior Youngster'=>['Senior Youngster'],
             ''];
    const PLAYER_EVENT_AGES_ISTD =
            ['Under 6'=>['Under 6'],
             'Under 8'=>['Under 8'],
             'Under 12'=>['Under 12'],
             'Junior 12-16'=>['Junior 12-16'],
             'Adult 16-50'=>['Adult 16-50'],
             'Senior 50'=>['Senior 50']];


    private static $domainValueHash;

    private static $valueById;

    /**
     * $playerLookup[$modelId][$genreId][$proficiencyId][$ageId][$typeId]=$playerId
     */
    private static $playerLookup;

    /** @var PlayerRepository */
    private static $playerRepository;

    /** @var EventRepository */
    private static $eventRepository;


    private static $competition;



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
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    public static function setUpBeforeClass()
    {
        (new Dotenv())->load( __DIR__ . '/../../../.env' );
        $kernel = self::bootKernel();
        $entityManagerModels = $kernel->getContainer()->get( 'doctrine.orm.models_entity_manager' );
        $entityManagerCompetition = $kernel->getContainer()->get('doctrine.orm.competition_entity_manager');
        self::initializeDatabase($entityManagerModels,'models.sql');
        self::initializeDatabase($entityManagerCompetition, 'competition-interface.sql');
        $competitionRepository = $entityManagerCompetition->getRepository(Competition::class);
        $modelRepository = $entityManagerCompetition->getRepository(Model::class);
        $models = $modelRepository->findAll();
        /** @var Model $model */
        foreach($models as $model){
            self::$playerLookup[$model->getId()]=$model->getPlayerlookup();
        }
        $valueRepository = $entityManagerModels->getRepository(Value::class);
        self::$domainValueHash = $valueRepository->fetchDomainValueHash();
        self::$valueById = $valueRepository->fetchAllValuesById();
        self::$competition = $competitionRepository->findOneBy(['name'=>'Georgia DanceSport Competition and Medal Exams']);
        self::$playerRepository = $entityManagerCompetition->getRepository( Player::class);
        self::$eventRepository = $entityManagerCompetition->getRepository(Event::class);
    }

    public function testConnect(){
        $this->assertTrue(true,'Connection successful');
    }

    public function testPlayerLookupVerify()
    {
        foreach(self::$playerLookup as $modelId=>$playerLookup){
            foreach($playerLookup as $genreId=>$forGenre){
                /** @var Value $genre */
                $genre = self::$valueById[$genreId];
                $genreName= $genre->getDomain()->getName();
                foreach($forGenre as $proficiencyId=>$forProficiency){
                    /** @var Value $proficiency */
                    $proficiency = self::$valueById[$proficiencyId];
                    foreach($forProficiency as $ageId=>$forAge){
                        /** @var Value $age */
                        $age = self::$valueById[$ageId];
                        foreach($forAge as $typeId=>$playerId){
                            /** @var Value $type */
                            $type=self::$valueById[$typeId];
                            /** @var Player $player */
                            $player = self::$playerRepository->findOneBy(['id'=>$playerId]);
                            $description=$player->getValue();
                            $messageGenre =  "Lookup $genreName =>".$genre->getName()
                                            ."Player $genreName=>".$description[$genreName];
                            $this->assertEquals($genre->getName(),$description[$genreName],$messageGenre);
                            $messageProficiency = "Lookup proficiency=>".$proficiency->getName()
                                                 ."Player proficiency=>".$description['proficiency'];
                            $this->assertEquals($proficiency->getName(),$description['proficiency'],$messageProficiency);
                            $messageAge = "Lookup age=>".$age->getName()
                                          ."Player age=>".$description['age'];
                            $this->assertEquals($age->getName(),$description['age'],$messageAge);
                            $messageType = "Lookup type=>".$type->getName()
                                          ."Player type=>".$description['type'];
                            $this->assertEquals($type->getName(),$description['type'],$messageType);

                        }
                    }
                }
            }
        }
    }

    /**
     * @param Player $player
     * @throws \Exception
     */
    private function assertPlayerEventFieldsOK(Player $player)
    {
        $missing = function ($cls, $id, $field) {
            return sprintf( '%s:%d missing %s', $cls, $id, $field );
        };
        $want = function ($id, $expect, $found) {
            return sprintf( 'Event:%d wanted %s, found %s', $id, $expect, $found );
        };
        $p = $player->getValue();
        $playerId = $player->getId();
        $this->assertTrue( isset( $p['genre'] ), $missing( 'Player', $playerId, 'genre' ) );
        $this->assertTrue( isset( $p['proficiency'] ), $missing( 'Player', $playerId, 'proficiency' ) );
        $this->assertTrue( isset( $p['age'] ), $missing( 'Player', $playerId, 'age' ) );
        $this->assertTrue( isset( $p['type'] ), $missing( 'Player', $playerId, 'type' ) );
        $model = $player->getModel();
        $events = self::$eventRepository->fetchEventsPreJSON( $model, $player);
        /** @var Event $event */
        foreach ($events as $id => $e) {
            $this->assertTrue( isset( $e['style'] ), $missing( 'Event', $id, 'style' ) );
            $this->assertTrue( isset( $e['proficiency'] ), $missing( 'Event', $id, 'proficiency' ) );
            $this->assertTrue( isset( $e['age'] ), $missing( 'Event', $id, 'age' ) );
            $this->assertTrue( isset( $e['type'] ), $missing( 'Event', $id, 'type' ) );
            $this->assertTrue( isset( $e['tag'] ), $missing( 'Event', $id, 'tag' ) );
            $this->assertTrue( isset( $e['dances'] ), $missing( 'Event', $id, 'dances' ) );
            switch ($p['genre']) {
                case 'American':
                case 'Rhythm':
                case 'Smooth':
                    $this->assertEquals( 'American', $e['style'], $want( $id, $p['genre'], 'American' ) );
                    break;
                case 'International':
                case 'Latin':
                case 'Standard':
                    $this->assertEquals( 'International', $e['style'], $want( $id, $p['genre'], 'International' ) );
                    break;
                case 'Fun Events':
                    $this->assertEquals( 'Fun Events', $e['style'], $want( $id, $p['genre'], 'Fun Events' ) );
                    break;
                case 'Novelty':
                    $this->assertEquals( 'Fun Events', $e['style'], $want( $id, $p['genre'], 'Fun Events' ) );
                    break;
                default:
                    ;
                    throw new \Exception( "PlayerId:$playerId did not have a genre of dance specified.", 9000 );
            }
        }
    }


    /**
     * @param Player $player
     * @throws \Exception
     */
    private function assertPlayerEventProficienciesOK(Player $player)
    {
        $eventProficienciesFn = function(string $modelName,array $p){
            switch($modelName){
                case 'ISTD Medal Exams':
                    return self::PLAYER_EVENT_ISTD[$p['proficiency']];
                case 'Georgia DanceSport Amateur':
                    return self::PLAYER_EVENT_AMATEUR[$p['proficiency']];
                case 'Georgia DanceSport ProAm':
                    return self::PLAYER_EVENT_PROAM[$p['proficiency']];
                default:
                    return null;
            }
        };

        $p = $player->getValue();
        $playerId = $player->getId();
        $model = $player->getModel();
        $events = self::$eventRepository->fetchEventsPreJSON( $model, $player);
        $acceptedEventProficiencies = $eventProficienciesFn($model->getName(),$p);
        if(in_array($p['genre'],['American','Rhythm','Smooth','International','Latin','Standard'])) {
            foreach($events as $id=>$e) {
                $message = sprintf('Player:%d does not have permission to enter Event:%d',$playerId,$id);
                $this->assertTrue(in_array($e['proficiency'],$acceptedEventProficiencies),$message);
            }
        } elseif (in_array($p['genre'],['Novelty','Fun Events'])) {
            foreach($events as $id=>$e) {
                $message = sprintf('Player:%d does not have permission to enter Event:%d',$playerId,$id);
                $this->assertTrue(in_array($e['proficiency'],$acceptedEventProficiencies),$message);
            }
        } else {
            throw new \Exception( "PlayerId:$playerId was without a genre.", 9000 );
        }
    }

    /**
     * @param Player $player
     */
    public function assertPlayerEventAgesOK(Player $player):void
    {
        $p = $player->getValue();
        $playerId = $player->getId();
        $model = $player->getModel();
        $events = self::$eventRepository->fetchEventsPreJSON( $model, $player);
        foreach($events as $id=>$e) {
            switch($model->getName()){
                case 'ISTD Medal Exams':
                    $acceptedAges = self::PLAYER_EVENT_AGES_ISTD[$p['age']];
                    $unacceptableAges = array_diff(array_keys($acceptedAges),$acceptedAges);
                    $message = sprintf('Player:%d does not have permission to enter Event:%d',$playerId,$id);
                    $this->assertTrue(in_array($e['age'],$acceptedAges),$message);
                    $this->assertFalse(in_array($e['age'],$unacceptableAges,$message));
                    break;
                case 'Georgia DanceSport Amateur':
                case 'Georgia DanceSport ProAm':
                    $acceptedAges = self::PLAYER_EVENT_AGES_USA[$p['age']];
                    $unacceptableAges = array_diff(array_keys($acceptedAges),$acceptedAges);
                    $message = sprintf('Player:%d does not have permission to enter Event:%d',$playerId,$id);
                    $this->assertTrue(in_array($e['age'],$acceptedAges),$message);
                    $this->assertFalse(in_array($e['age'],$unacceptableAges,$message));
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function testPlayerEventVerify()
    {
        $players=self::$playerRepository->findAll();
        /** @var Player $player */
        foreach($players as $player) {
            $this->assertPlayerEventFieldsOK( $player );
            $this->assertPlayerEventProficienciesOK( $player );
            $this->assertPlayerEventAgesOK($player);
        }
    }
}