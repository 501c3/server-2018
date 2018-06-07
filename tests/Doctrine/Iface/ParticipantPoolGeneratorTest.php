<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/23/18
 * Time: 11:05 AM
 */

namespace App\Tests\Doctrine\Iface;


use App\Entity\Competition\Competition;
use App\Entity\Competition\Iface;
use App\Entity\Competition\Model;
use App\Entity\Models\Value;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class ParticipantPoolGeneratorTest extends KernelTestCase
{

    /** @var EntityManagerInterface */
    private static $entityManagerCompetition;

    /** @var EntityManagerInterface */
    private static $entityManagerModels;

    /** @var array */
    private static $domainValueHash;


    /** @var ParticipantPoolGenerator */
    private $participantPoolGenerator;

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
        $sql = file_get_contents( __DIR__ . '/../../Scripts/SQL/' .$dataFile );
        $connection->query( $sql );
    }

    public static function setUpBeforeClass()
    {
        (new Dotenv())->load( __DIR__ . '/../../../.env' );
        $kernel = self::bootKernel();
        self::$entityManagerCompetition = $kernel->getContainer()->get('doctrine.orm.competition_entity_manager');
        self::$entityManagerModels = $kernel->getContainer()->get('doctrine.orm.models_entity_manager');
        $valueRepository = self::$entityManagerModels->getRepository(Value::class);
        self::$domainValueHash = $valueRepository->fetchDomainValueHash();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp()
    {
        self::initializeDatabase(self::$entityManagerModels,'models.sql');
        self::initializeDatabase(self::$entityManagerCompetition,'competition-sequence.sql');
        $competitionRepository = self::$entityManagerCompetition->getRepository(Competition::class);
        $modelRepository = self::$entityManagerCompetition->getRepository(Model::class);
        $ifaceRepository = self::$entityManagerCompetition->getRepository(Iface::class);
        $valueRepository = self::$entityManagerModels->getRepository(Value::class);
        $this->participantPoolGenerator = new ParticipantPoolGenerator($competitionRepository,
                                                                        $modelRepository,
                                                                        $ifaceRepository,
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
        $this->participantPoolGenerator->parse( $yamlText );
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
        $this->participantPoolGenerator->parse( $yamlText );
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
        $this->participantPoolGenerator->parse($yamlText);
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
        $this->participantPoolGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not participant-pool" at row:6, col:1 expected "participant-pool".
     * @expectedExceptionCode 6010
     */
    public function test6010ExceptionParticipantPool()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/pool-6010-exception-participant-pool.yml' );
        $this->participantPoolGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid key" at row:10, col:9 expected "genres","proficiencies","ages","sex","type".
     * @expectedExceptionCode 6100
     */
    public function test6100ExceptionInvalidKey()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/pool-6100-exception-invalid-key.yml' );
        $this->participantPoolGenerator->parse($yamlText);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Genre" at row:8, col:25 is invalid.
     * @expectedExceptionCode 6102
     */
    public function test6102ExceptionInvalidGenre()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/pool-6102-exception-invalid-genre.yml' );
        $this->participantPoolGenerator->parse($yamlText);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage  "1190" at row:16, col:15 invalid age range.
     * @expectedExceptionCode 6106
     */
    public function test6104ExceptionInvalidRange()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/pool-6104-exception-invalid-range.yml' );
        $this->participantPoolGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "11-3" at row:10, col:15 invalid age range.
     * @expectedExceptionCode 6106
     */
    public function test6106ExceptionAgeRange()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/pool-6106-exception-age-range.yml' );
        $this->participantPoolGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "L" at row:17, col:15 expected M and/or F.
     * @expectedExceptionCode 6108
     */

    public function test6108ExceptionInvalidSex()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/pool-6108-exception-invalid-sex.yml' );
        $this->participantPoolGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Type" at row:12, col:15 is invalid.
     * @expectedExceptionCode 6110
     */
    public function test6110ExceptionInvalidType()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/pool-6110-exception-invalid-type.yml' );
        $this->participantPoolGenerator->parse($yamlText);
    }

    public function testParticipantPoolGenerate()
    {
        $fileLocation = realpath( __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/participant-pool.yml' );
        $yamlText =  file_get_contents($fileLocation);
        $participantPool = $this->participantPoolGenerator->parse($yamlText);
        foreach($participantPool as $genre=>$proficiencyList){
            foreach($proficiencyList as $proficiency=>$ageList){
                foreach($ageList as $age=>$sexList){
                    foreach($sexList as $sex=>$typeList){
                        foreach($typeList as $type=>$participant){
                            $genreValue=isset(self::$domainValueHash['style'][$genre])?
                                self::$domainValueHash['style'][$genre]:
                                self::$domainValueHash['substyle'][$genre];
                            $proficiencyValue = self::$domainValueHash['proficiency'][$proficiency];
                            $this->assertAttributeEquals($sex,'sex',$participant);
                            $this->assertAttributeEquals("$genre-$proficiency-$age",
                                'first', $participant);
                            $this->assertAttributeEquals("$sex-$type", 'last', $participant);
                            $genreProficiencies = $participant->getGenreProficiency()->toArray();
                            $expected = [$genreValue->getId()=>$proficiencyValue->getId()];
                            $this->assertEquals($expected,$genreProficiencies);
                        }
                    }
                }
            }
        }
    }
}