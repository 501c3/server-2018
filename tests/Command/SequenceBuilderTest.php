<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/16/18
 * Time: 11:16 AM
 */

namespace App\Tests\Command;


use App\Command\CompetitionSequenceBuild;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Dotenv\Dotenv;

class SequenceBuilderTest extends KernelTestCase
{

    protected static $kernel;

    private static function purgeDatabase(EntityManagerInterface $entityManager){
        $purger = new ORMPurger($entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $conn = $purger->getObjectManager()->getConnection();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public static function setUpBeforeClass()
    {
        // TODO: Remove next line when environment variables are set up make conditional for production
        (new Dotenv())->load(__DIR__.'/../../.env');
        // TODO: Change dev->prod, false for production
        self::$kernel = self::bootKernel();
        $container = self::$kernel->getContainer();
        $emModels = $container->get('doctrine.orm.models_entity_manager');
        $emComp = $container->get('doctrine.orm.competition_entity_manager');
        self::purgeDatabase($emModels);
        self::purgeDatabase($emComp);
        $connection = $emModels->getConnection();
        $sql = file_get_contents( __DIR__ . '/../Scripts/SQL/models.sql' );
        $connection->query( $sql );
    }


    /**
     * @throws \Doctrine\DBAL\DBALException
     */

    public function setUp()
    {
        $container = self::$kernel->getContainer();
        $emComp = $container->get('doctrine.orm.competition_entity_manager');
        self::purgeDatabase($emComp);

    }

    private function commandBuildCompetition($testSource)
    {
        $application = new Application('Competition Test Build');
        $command = new CompetitionSequenceBuild();
        $application->add($command);
        $commandTester = new CommandTester($command);
        $executionItem=[
            'command'=>$command->getName(),
            'filename'=> __DIR__ . "/../Scripts/Yaml/Competition/$testSource"
        ];
        $commandTester->execute($executionItem);
        $output=$commandTester->getDisplay();
        return $output;
    }

    public function testConnect()
    {
        $output=$this->commandBuildCompetition('sequence-correct.yml');
        $message = 'Commencing at';
        $this->assertContains($message,$output);
    }

}