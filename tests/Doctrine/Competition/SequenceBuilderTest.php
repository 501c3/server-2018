<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/18/18
 * Time: 10:13 AM
 */

namespace App\Tests\Doctrine\Competition;
use App\Doctrine\Competition\SequenceBuilder;
use App\Entity\Competition\Competition;
use App\Entity\Competition\Model as CompetitionModel;
use App\Entity\Competition\Player as CompetitionPlayer;
use App\Entity\Competition\Schedule;
use App\Entity\Competition\Event as CompetitionEvent;
use App\Entity\Competition\Session;
use App\Entity\Competition\Subevent as CompetitionSubevent;
use App\Entity\Models\Domain;
use App\Entity\Models\Event;
use App\Entity\Models\Model;
use App\Entity\Models\Player;
use App\Entity\Models\Subevent;
use App\Entity\Models\Value;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\EventRepository as CompetitionEventRepository;
use App\Repository\Competition\ModelRepository as CompetitionModelRepository;
use App\Repository\Competition\PlayerRepository as CompetitionPlayerRepository;
use App\Repository\Competition\ScheduleRepository;
use App\Repository\Competition\SessionRepository;
use App\Repository\Competition\SubeventRepository as CompetitionSubeventRepository;
use App\Repository\Models\DomainRepository;
use App\Repository\Models\EventRepository;
use App\Repository\Models\ModelRepository;
use App\Repository\Models\PlayerRepository;
use App\Repository\Models\SubeventRepository;
use App\Repository\Models\ValueRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class SequenceBuilderTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private static $entityManagerModel;

    /** @var EntityManagerInterface */
    private static $entityManagerCompetition;

    /** @var SequenceBuilder */
    private $competitionBuilder;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function setUpBeforeClass()
    {
        (new Dotenv())->load( __DIR__ . '/../../../.env' );
        $kernel = self::bootKernel();
        self::$entityManagerModel = $kernel->getContainer()->get( 'doctrine.orm.models_entity_manager' );
        self::$entityManagerCompetition = $kernel->getContainer()->get( 'doctrine.orm.competition_entity_manager' );
        $purgerModel = new ORMPurger( self::$entityManagerModel );
        $purgerModel->setPurgeMode( ORMPurger::PURGE_MODE_TRUNCATE );
        $connectionModel = $purgerModel->getObjectManager()->getConnection();
        $connectionModel->query( 'SET FOREIGN_KEY_CHECKS=0' );
        $purgerModel->purge();
        $connectionModel->query( 'SET FOREIGN_KEY_CHECKS=1' );
        // Rebuild the Model Database from scratch
        $sql = file_get_contents( __DIR__ . '/../../Scripts/SQL/models.sql' );
        $connectionModel->query( $sql );

    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function setUp()
    {
        $purgerCompetition = new ORMPurger( self::$entityManagerCompetition );
        $purgerCompetition->setPurgeMode( ORMPurger::PURGE_MODE_TRUNCATE );
        $connectionCompetition = $purgerCompetition->getObjectManager()->getConnection();
        $connectionCompetition->executeQuery( 'SET FOREIGN_KEY_CHECKS=0' );
        $purgerCompetition->purge();
        $connectionCompetition->executeQuery( 'SET FOREIGN_KEY_CHECKS=1' );

        /** @var ModelRepository $modelRepository */
        $modelRepository = self::$entityManagerModel->getRepository( Model::class );
        /** @var DomainRepository $domainRepository */
        $domainRepository = self::$entityManagerModel->getRepository( Domain::class );
        /** @var ValueRepository $valueRepository */
        $valueRepository = self::$entityManagerModel->getRepository( Value::class );
        /** @var PlayerRepository $modelPlayerRepository */
        $modelPlayerRepository = self::$entityManagerModel->getRepository( Player::class );
        /** @var EventRepository $modelEventRepository */
        $modelEventRepository = self::$entityManagerModel->getRepository( Event::class );
        /** @var SubeventRepository $modelSubeventRepository */
        $modelSubeventRepository = self::$entityManagerModel->getRepository( Subevent::class );
        /** @var CompetitionRepository $competitionRepository */
        $competitionRepository = self::$entityManagerCompetition->getRepository( Competition::class );
        /** @var CompetitionModelRepository $modelRepository */
        $competitionModelRepository = self::$entityManagerCompetition->getRepository( CompetitionModel::class );
        /** @var CompetitionPlayerRepository $competitionPlayerRepository */
        $competitionPlayerRepository = self::$entityManagerCompetition->getRepository( CompetitionPlayer::class );
        /** @var CompetitionEventRepository $competitionEventRepository */
        $competitionEventRepository = self::$entityManagerCompetition->getRepository( CompetitionEvent::class );
        /** @var CompetitionSubeventRepository $competitionSubeventRepository */
        $competitionSubeventRepository = self::$entityManagerCompetition->getRepository( CompetitionSubevent::class );
        /** @var SessionRepository $sessionRepository */
        $sessionRepository = self::$entityManagerCompetition->getRepository( Session::class );
        /** @var ScheduleRepository $scheduleRepository */
        $scheduleRepository = self::$entityManagerCompetition->getRepository( Schedule::class );

        $this->competitionBuilder = new SequenceBuilder(
            $modelRepository,
            $domainRepository,
            $valueRepository,
            $modelPlayerRepository,
            $modelEventRepository,
            $modelSubeventRepository,
            $competitionRepository,
            $competitionModelRepository,
            $competitionPlayerRepository,
            $competitionEventRepository,
            $competitionSubeventRepository,
            $sessionRepository,
            $scheduleRepository );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_competition" at row:4, col:1 expected "competition".
     * @expectedExceptionCode 3002
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test3002ExceptionCompetition()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/sequence-3002-exception-competition.yml' );
        $this->competitionBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_models" at row:5, col:1 expected "models".
     * @expectedExceptionCode 3102
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test3102ExceptionModels()
    {
        $yamlText = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Competition/sequence-3102-exception-models.yml' );
        $this->competitionBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid_model" at row:5, col:37 is an invalid model.
     * @expectedExceptionCode 3104
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test3104ExceptionInvalidModel()
    {
        $yamlText = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Competition/sequence-3104-exception-invalid-model.yml' );
        $this->competitionBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_sequence" at row:6, col:1 expected "sequence".
     * @expectedExceptionCode 3202
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test3202ExceptionSequence()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/sequence-3202-exception-sequence.yml' );
        $this->competitionBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "unrecognized_model" at row:9, col:17 is not recognized.
     * @expectedExceptionCode 3204
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test3204ExceptionUnrecognizedModel()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/sequence-3204-exception-unrecognized-model.yml' );
        $this->competitionBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid_style" at row:10, col:21 invalid style.
     * @expectedExceptionCode 3206
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test3206ExceptionInvalidStyle()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/sequence-3206-exception-invalid-style.yml' );
        $this->competitionBuilder->build( $yamlText );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid_substyle" at row:11, col:25 invalid substyle.
     * @expectedExceptionCode 3208
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test3208ExceptionInvalidSubstyle()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/sequence-3208-exception-invalid-substyle.yml' );
        $this->competitionBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid_proficiency" at row:12, col:29 invalid proficiency.
     * @expectedExceptionCode 3210
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test3210ExceptionInvalidProficiency()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/sequence-3210-exception-invalid-proficiency.yml' );
        $this->competitionBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid_age" at row:12, col:50 invalid age.
     * @expectedExceptionCode 3212
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException

     */
    public function test3212ExceptionInvalidAge()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/sequence-3212-exception-invalid-age.yml' );
        $this->competitionBuilder->build( $yamlText );
    }

    /**
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testSequenceCorrect()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/sequence-correct.yml' );
        $this->competitionBuilder->build( $yamlText );
        $this->assertTrue(true);
    }

}