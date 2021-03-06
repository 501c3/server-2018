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
use App\Exceptions\GeneralException;
use App\Repository\Competition\IfaceRepository;
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

    /** @var IfaceRepository */
    private static $ifaceRepository;

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
        self::initializeDatabase($entityManagerCompetition, 'competition-sequence.sql');
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
        self::$ifaceRepository=$ifaceRepository;
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_competition" at row:4, col:1 expected "competition".
     * @expectedExceptionCode 4102
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4102ExceptionCompetitionKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4102-exception-competition-keyword.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Competition Invalid" at row:4, col:14 not found.
     * @expectedExceptionCode 4104
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4104ExceptionCompetitionInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4104-exception-competition-invalid.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_models" at row:5, col:1 expected "models".
     * @expectedExceptionCode 4202
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4202ExceptionModelsKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4202-exception-models-keyword.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Model" at row:5, col:37 is an invalid model.
     * @expectedExceptionCode 4204
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4204ExceptionModelInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4204-exception-model-invalid.yml');
        $this->interfaceBuilder->build( $yamlText );

    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_setups" at row:6, col:1 expected "setups".
     * @expectedExceptionCode 4302
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4310ExceptionSetupKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4302-exception-setups-keyword.yml');
        $this->interfaceBuilder->build( $yamlText );
    }



    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_participant-form" at row:7, col:5 expected "participants".
     * @expectedExceptionCode 4310
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4310ExceptionPFormKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4310-exception-pfrm-keyword.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_typeA" at row:8, col:9 expected "typeA".
     * @expectedExceptionCode 4312
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4312ExceptionTypeAKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4312-exception-typeA-keyword.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:8, col:30 expected "Professional" or "Amateur".
     * @expectedExceptionCode 4314
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4314ExceptionTypeAInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4314-exception-typeA-invalid.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_typeB" at row:9, col:9 expected "typeB".
     * @expectedExceptionCode 4316
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4316ExceptionTypeBKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4316-exception-typeB-keyword.yml');
        $this->interfaceBuilder->build( $yamlText );

    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:9, col:26 expected "Teacher" or "Student".
     * @expectedExceptionCode 4318
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4318ExceptionTypeBInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4318-exception-typeB-invalid.yml');
        $this->interfaceBuilder->build( $yamlText );

    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage  "not_proficiency-dropdown" at row:10, col:9 expected "proficiency-dropdown"
     * @expectedExceptionCode 4400
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4400ExceptionProfDropKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4400-profdrop-keyword.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:11, col:13 expected "Professional","Amateur".
     * @expectedExceptionCode 4402
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4402ExceptionDropAInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4402-exception-dropA-invalid.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage  "Invalid" at row:12, col:17 expected "Teacher","Student".
     * @expectedExceptionCode 4404
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4404ExceptionDropBInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4404-exception-dropB-invalid.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Model" at row:13, col:21 expected "ISTD Medal Exams","Georgia DanceSport Amateur","Georgia DanceSport ProAm".
     * @expectedExceptionCode 4406
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4406ExceptionDropModelInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4406-exception-drop-model-invalid.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_genres" at row:14, col:25 expected "genres".
     * @expectedExceptionCode 4408
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4408ExceptionGenresKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4408-exception-genres-keyword.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_proficiencies" at row:15, col:25 expected "proficiencies".
     * @expectedExceptionCode 4410
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4410ExceptionProficienciesKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4410-exception-proficiencies-keyword.yml');
        $this->interfaceBuilder->build( $yamlText );

    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:14, col:41 is invalid.
     * @expectedExceptionCode 4412
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4412ExceptionGenreInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4412-exception-genre-invalid.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:15, col:54 is invalid.
     * @expectedExceptionCode 4414
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4414ExceptionProficiencyInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4414-exception-proficiency-invalid.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_ti-proficiency-dropdown" at row:66, col:9 expected "ti-proficiency-dropdown".
     * @expectedExceptionCode 4416
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4416ExceptionTiProficiencyKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4416-exception-ti-proficiency-keyword.yml');
        $this->interfaceBuilder->build( $yamlText );

    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Model" at row:67, col:14 is invalid.
     * @expectedExceptionCode 4418
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test4418ExceptionTiModelInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-4418-exception-ti-model-invalid.yml');
        $this->interfaceBuilder->build( $yamlText );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_mappings" at row:70, col:1 expected "mappings".
     * @expectedExceptionCode 5002
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test5002ExceptionMappingsKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-5002-exception-mappings-keyword.yml');
        $this->interfaceBuilder->build( $yamlText );

    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_proficiency" at row:71, col:5 expected "proficiency".
     * @expectedExceptionCode 5102
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test5102ExceptionProficiencyKeyword()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-5102-exception-proficiency-keyword.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Model" at row:72, col:9 is invalid model.
     * @expectedExceptionCode 5112
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    public function test5112ExceptionModelInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-5112-exception-model-invalid.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Model" at row:73, col:13 is invalid.
     * @expectedExceptionCode 5122
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    public function test5122ExceptionModelInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-5122-exception-model-invalid.yml' );
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:74, col:17 is invalid.
     * @expectedExceptionCode 5124
     *
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test5124ExceptionProficiencyInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-5124-exception-proficiency-invalid.yml');
        $this->interfaceBuilder->build( $yamlText );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid" at row:74, col:29 is invalid.
     * @expectedExceptionCode 5126
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test5126ExceptionProficiencyInvalid()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-5126-exception-proficiency-invalid.yml');
        $this->interfaceBuilder->build( $yamlText );

    }

    /**
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testCorrect()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Competition/interface-correct.yml' );
        $this->interfaceBuilder->build( $yamlText );
        $result=self::$ifaceRepository->findAll();
        $this->assertEquals(1,count($result));
    }

}