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
use App\Entity\Competition\Iface;
use App\Entity\Competition\Model;
use App\Entity\Models\Value;
use App\Repository\Competition\CompetitionRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class PlayerPoolGeneratorTest extends KernelTestCase
{
    /** @var ParticipantPoolGenerator */
    private static $participantPool;

    /** @var PlayerPoolGenerator */
    private  static $playerPool;

    /** @var Classify */
    private static $classify;

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
        self::initializeDatabase($entityManagerModels,'models.sql');
        self::initializeDatabase($entityManagerCompetition,'competition-interface.sql');
        /** @var CompetitionRepository $competitionRepository */
        $competitionRepository=$entityManagerCompetition->getRepository(Competition::class);
        $modelRepository=$entityManagerCompetition->getRepository(Model::class);
        $ifaceRepository=$entityManagerCompetition->getRepository(Iface::class);
        $valueRepository=$entityManagerModels->getRepository(Value::class);

        $poolGenerator = new ParticipantPoolGenerator($competitionRepository,
                                                    $modelRepository,
                                                    $valueRepository);

        $fileLocation = realpath( __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/participant-pool.yml' );
        $yamlText =  file_get_contents($fileLocation);
        self::$participantPool = $poolGenerator->parse($yamlText);
        self::$playerPool = new PlayerPoolGenerator($competitionRepository,
                                                    $modelRepository,
                                                    $valueRepository);
        self::$playerPool->setParticipantPool(self::$participantPool);


        self::$classify = new Classify($competitionRepository,$modelRepository,$ifaceRepository,$valueRepository);
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
    public function test6004ExceptionInvalidCompetition()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/base-6004-exception-invalid-competition.yml' );
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no model" at row:5, col:1 expected "models".
     * @expectedExceptionCode 6006
     */
    public function test6006ExceptionModels()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/base-6006-exception-models.yml' );
        self::$playerPool->parse($yamlText);
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
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no genres" at row:6, col:1 expected "genres".
     * @expectedExceptionCode 7002
     */
    public function test7002ExceptionGenres()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7002-exception-genres.yml' );
        self::$playerPool->parse($yamlText);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Genre" at row:6, col:17 is invalid.
     * @expectedExceptionCode 7004
     */
    public function test7004ExceptionInvalidGenre()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7004-exception-invalid-genre.yml');
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no couples" at row:7, col:1 expected "couples".
     * @expectedExceptionCode 7102
     */
    public function test7102ExceptionCouples()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7102-exception-couples.yml');
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no lead" at row:9, col:9 expected "lead".
     * @expectedExceptionCode 7202
     */
    public function test7202ExceptionLead()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7202-exception-lead.yml');
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no follow" at row:10, col:9 expected "follow".
     * @expectedExceptionCode 7204
     */
    public function test7204ExceptionFollow()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7204-exception-follow.yml');
        self::$playerPool->parse($yamlText);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "badkey" at row:9, col:16 expected "proficiency","age","sex","type","expected".
     * @expectedExceptionCode 7210
     */
    public function test7210ExceptionKey()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7210-exception-key.yml');
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Proficiency" at row:9, col:29 invalid proficiency.
     * @expectedExceptionCode 7212
     */
    public function test7212ExceptionInvalidProficiency()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7212-invalid-proficiency.yml');
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "X" at row:9, col:44 expected number.
     * @expectedExceptionCode 7214
     */
    public function test7214ExceptionInvalidAge()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7214-exception-invalid-age.yml');
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "B" at row:9, col:52 expected "M" or "F".
     * @expectedExceptionCode 7216
     */
    public function test7216ExceptionInvalidSex()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7216-exception-invalid-sex.yml');
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Type" at row:9, col:61 invalid type.
     * @expectedExceptionCode 7218
     */
    public function test7218ExceptionInvalidType()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7218-exception-invalid-type.yml');
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid_key" at row:16, col:28 expected "proficiency","age","type".
     * @expectedExceptionCode 7310
     */
    public function test7310ExceptionInvalidExpectedKeys()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7310-exception-invalid-expected-keys.yml');
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage  "Invalid Proficiency" at row:16, col:41 is invalid proficiency.
     * @expectedExceptionCode 7312
     */
    public function test7312ExceptionInvalidProficiency()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7312-exception-invalid-proficiency.yml');
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:16, col:56 is invalid age.
     * @expectedExceptionCode 7314
     */
    public function test7314ExceptionInvalidAge()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7314-exception-invalid-age.yml');
        self::$playerPool->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:16, col:68 is invalid type.
     * @expectedExceptionCode 7316
     */
    public function test7316ExceptionInvalidType()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/pool-7316-exception-invalid-type.yml');
        self::$playerPool->parse($yamlText);
    }


    public function testCouplesPlayer()
    {
        $yamlText = file_get_contents(__DIR__ . '/../../Scripts/Yaml/Iface/PlayerPool/player-pool.yml' );
        $results=self::$playerPool->parse($yamlText);
        foreach($results as $coupling) {
            $lead = $coupling['leader'];
            $follow = $coupling['follower'];

            $expected = $coupling['expected'];
        }
    }


}