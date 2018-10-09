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
use App\Entity\Sales\Iface\Participant;
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
        $purger = new ORMPurger( $entityManager );
        $purger->setPurgeMode( ORMPurger::PURGE_MODE_TRUNCATE );
        $connection = $purger->getObjectManager()->getConnection();
        $connection->query( 'SET FOREIGN_KEY_CHECKS=0' );
        $purger->purge();
        $connection->query( 'SET FOREIGN_KEY_CHECKS=1' );
        $sql = file_get_contents( __DIR__ . '/../../Scripts/SQL/' . $dataFile );
        $connection->query( $sql );
    }

    public static function setUpBeforeClass()
    {
        (new Dotenv())->load( __DIR__ . '/../../../.env' );
        $kernel = self::bootKernel();
        self::$entityManagerCompetition = $kernel->getContainer()->get( 'doctrine.orm.competition_entity_manager' );
        self::$entityManagerModels = $kernel->getContainer()->get( 'doctrine.orm.models_entity_manager' );
        $valueRepository = self::$entityManagerModels->getRepository( Value::class );
        self::$domainValueHash = $valueRepository->fetchDomainValueHash();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp()
    {
        self::initializeDatabase( self::$entityManagerModels, 'models.sql' );
        self::initializeDatabase( self::$entityManagerCompetition, 'competition-sequence.sql' );
        $competitionRepository = self::$entityManagerCompetition->getRepository( Competition::class );
        $modelRepository = self::$entityManagerCompetition->getRepository( Model::class );
        $ifaceRepository = self::$entityManagerCompetition->getRepository( Iface::class );
        $valueRepository = self::$entityManagerModels->getRepository( Value::class );
        $this->participantPoolGenerator = new ParticipantPoolGenerator( $competitionRepository,
                                                                        $modelRepository,
                                                                        $ifaceRepository,
                                                                        $valueRepository );
    }


    /**
     * @throws \App\Exceptions\GeneralException
     */
    public function testParticipantPoolGenerate()
    {
        $fileLocation = realpath( __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/participant-pool.yml' );
        $yamlText = file_get_contents( $fileLocation );
        $participantPool = $this->participantPoolGenerator->parse( $yamlText );
        foreach ($participantPool as $poolName => $typeAList) {
            foreach ($typeAList as $typeA => $typeBList) {
                foreach ($typeBList as $typeB => $sexList) {
                    foreach ($sexList as $sex => $genreList) {
                        foreach ($genreList as $genre => $proficiencyList) {
                            /** @var Value $genreValue */
                            foreach ($proficiencyList as $proficiency => $ageList) {
                                /** @var Value $proficiencyValue */

                                /**
                                 * @var string  $age
                                 * @var  Participant $participant
                                 */
                                foreach ($ageList as $age => $participant) {
                                    $this->assertAttributeEquals( $sex, 'sex', $participant );
                                    $this->assertAttributeEquals( "$genre-$proficiency",
                                                                    'first', $participant );
                                    $this->assertAttributeEquals(
                                        "$typeA-$sex$age",
                                        'last', $participant );
                                    $this->assertAttributeEquals($age,'years',$participant);
                                    $this->assertAttributeEquals($sex, 'sex',$participant);


                                    $description = $participant->describe();
                                    $this->assertArraySubset(['genreProficiency'=>[$genre=>$proficiency]],$description);
                                }
                            }
                        }

                    }
                }
            }
        }
    }
}