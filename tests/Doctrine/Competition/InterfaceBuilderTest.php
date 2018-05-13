<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/9/18
 * Time: 9:48 AM
 */

namespace App\Tests\Doctrine\Competition;


use App\Doctrine\Competition\InterfaceBuilder;
use App\Entity\Competition\Competition;
use App\Entity\Models\Domain;
use App\Entity\Models\Model;
use App\Entity\Models\Value;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Competition\Iface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class InterfaceBuilderTest extends KernelTestCase
{
    /** @var InterfaceBuilder */
    private $interfaceBuilder;

    /** @var EntityManagerInterface */
    private static $entityManagerCompetition;

    /** @var EntityManagerInterface */
    private static $entityManagerModels;


    /**
     * @param EntityManagerInterface $entityManager
     * @param $dataFile
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function initializeDatabase(EntityManagerInterface $entityManager, $dataFile)
    {
        $purger = new ORMPurger($entityManager);
        $purger->setPurgeMode( ORMPurger::PURGE_MODE_TRUNCATE );
        $connection = $purger->getObjectManager()->getConnection();
        $connection->query( 'SET FOREIGN_KEY_CHECKS=0' );
        $purger->purge();
        $connection->query( 'SET FOREIGN_KEY_CHECKS=1' );
        $sql = file_get_contents( __DIR__ . '/../../Scripts/SQL/'.$dataFile );
        $connection->query( $sql );
    }


    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function setUpBeforeClass()
    {
        (new Dotenv())->load( __DIR__ . '/../../../.env' );
        $kernel = self::bootKernel();
        $entityManagerModel = $kernel->getContainer()->get( 'doctrine.orm.models_entity_manager' );
        $entityManagerCompetition = $kernel->getContainer()->get( 'doctrine.orm.competition_entity_manager' );
        self::initializeDatabase($entityManagerModel,'models.sql');
        self::initializeDatabase($entityManagerCompetition, 'competition.sql');
        self::$entityManagerCompetition = $entityManagerCompetition;
        self::$entityManagerModels = $entityManagerModel;
    }


    protected function setUp()
    {
        $modelRepository =  self::$entityManagerModels->getRepository(Model::class);
        $domainRepository = self::$entityManagerModels->getRepository(Domain::class);
        $valueRepository =  self::$entityManagerModels->getRepository(Value::class);
        $competitionRepository = self::$entityManagerCompetition->getRepository(Competition::class);
        $ifaceRepository = self::$entityManagerCompetition->getRepository(Iface::class);
        $this->interfaceBuilder = new InterfaceBuilder($modelRepository,
                                                        $domainRepository,
                                                        $valueRepository,
                                                        $competitionRepository,
                                                        $ifaceRepository);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_competition" at row:4, col:1 expected "competition".
     * @expectedExceptionCode 4102
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4102ExceptionCompetition()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4102-exception-competition.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Competition" at row:4, col:14 not found.
     * @expectedExceptionCode 4104
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4104ExceptionInvalidCompetition()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4104-exception-invalid-competition.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_models" at row:5, col:1 expected "models".
     * @expectedExceptionCode 4202
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4202ExceptionModels()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4202-exception-models.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Model" at row:5, col:37 is an invalid model.
     * @expectedExceptionCode 4204
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4204ExceptionInvalidModel()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4204-exception-invalid-model.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_setups" at row:6, col:1 expected "setups".
     * @expectedExceptionCode 4302
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4302ExceptionSetups()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4302-exception-setups.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_participants" at row:7, col:5 expected "participants".
     * @expectedExceptionCode 4304
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4304ExceptionParticipants()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4304-exception-participants.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage  "no_type" at row:8, col:9 expected "type".
     * @expectedExceptionCode 4306
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4306ExceptionType()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4306-exception-type.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:10, col:15 is invalid.
     * @expectedExceptionCode 4308
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4308ExceptionTypeValue()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4308-exception-type-value.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:18, col:13 is invalid.
     * @expectedExceptionCode 4312
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4312ExceptionProficiencyType()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4312-exception-proficiency-type.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Proficiency" at row:19, col:19 is invalid.
     * @expectedExceptionCode 4314
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4314ExceptionInvalidProficiency()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4314-exception-invalid-proficiency.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_combinations" at row:44, col:9 expected proficiency-combinations.
     * @expectedExceptionCode 4402
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    public function test4402ExceptionCombinations()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4402-exception-combinations.yml');
        $this->interfaceBuilder->build( $yamlText );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Not Teacher" at row:46, col:17 is invalid.
     * @expectedExceptionCode 4404
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4404ExceptionValidCombinationType()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4404-exception-valid-combination.yml');
        $this->interfaceBuilder->build( $yamlText );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_mapping" at row:51, col:1 expected "mappings".
     * @expectedExceptionCode 4502
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4502ExceptionMappings()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4502-exception-mappings.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_genre" at row:52, col:5 expected "genre".
     * @expectedExceptionCode 4504
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4504ExceptionGenre()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4504-exception-genre.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:52, col:39 is invalid.
     * @expectedExceptionCode 4506
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4506ExceptionInvalidGenreStyle()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4506-exception-invalid-genre-style.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:52, col:31 is invalid.
     * @expectedExceptionCode 4506
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4506ExceptionInvalidGenreSubstyle()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4506-exception-invalid-genre-substyle.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_proficiency" at row:53, col:5 expected "proficiency".
     * @expectedExceptionCode 4512
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4512ExceptionMappingProficiency()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4512-exception-mapping-proficiency.yml');
        $this->interfaceBuilder->build( $yamlText );

    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "No Student" at row:54, col:9 left type is invalid.
     * @expectedExceptionCode 4514
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4514ExceptionMappingTypeLeft()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4514-exception-mapping-type-left.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "No Amateur" at row:54, col:9 right type is invalid.
     * @expectedExceptionCode 4514
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4514ExceptionMappingTypeRight()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4514-exception-mapping-type-right.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Bronze" at row:57, col:13 is not valid for Student.
     * @expectedExceptionCode 4516
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4516ExceptionMappingLeftCheck()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4516-exception-mapping-left-check.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Full Silver" at row:59, col:25 is not valid for Amateur.
     * @expectedExceptionCode 4516
     *
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4516ExceptionMappingRightCheck()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4516-exception-mapping-right-check.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_age" at row:69, col:5 expected "age".
     * @expectedExceptionCode 4520
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4520ExceptionMappingAge()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4520-exception-mapping-age.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:70, col:9 is invalid.
     * @expectedExceptionCode 4522
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4522ExceptionMappingTypeAge()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4522-exception-mapping-type-age.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "1-B" at row:71, col:13 error in age spread.
     * @expectedExceptionCode 4524
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4524ExceptionMappingAgeSpread()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4524-exception-mapping-age-spread.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:72, col:18 is invalid.
     * @expectedExceptionCode 4526
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    public function test4526ExceptionMappingInvalidAge()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4526-exception-mapping-invalid-age.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "4-6" at row:72, col:13 age spread overlap.
     * @expectedExceptionCode 4528
     *
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4528ExceptionMappingSpreadOverlap()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4528-exception-mapping-spread-overlap.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testCorrect()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-correct.yml' );
        $this->interfaceBuilder->build( $yamlText );
        $this->assertTrue(true);
    }

}