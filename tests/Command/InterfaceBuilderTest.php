<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/13/18
 * Time: 1:19 AM
 */

namespace App\Tests\Command;


use App\Command\CompetitionInterfaceBuild;
use App\Kernel;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Driver\PDOSqlsrv\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Dotenv\Dotenv;

class InterfaceBuilderTest extends KernelTestCase
{
    /** @var Kernel */
    protected static $kernel;

    /**
     * @param EntityManagerInterface $entityManager
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function purgeDatabase(EntityManagerInterface $entityManager){
        $purger = new ORMPurger($entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $conn = $purger->getObjectManager()->getConnection();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function setUpBeforeClass()
    {
        (new Dotenv())->load(__DIR__.'/../../.env');
        self::$kernel = self::bootKernel();
        $container = self::$kernel->getContainer();
        $emModels = $container->get('doctrine.orm.models_entity_manager');
        $emComp = $container->get('doctrine.orm.competition_entity_manager');
        self::purgeDatabase($emModels);
        self::purgeDatabase($emComp);
        /** @var Connection $conn */
        $conn=$emModels->getConnection();
        $sqlModels = file_get_contents( __DIR__ . '/../Scripts/SQL/models.sql' );
        $conn->query( $sqlModels );
        $sqlSequence = file_get_contents(__DIR__ . '/../Scripts/SQL/competition-sequence.sql' );
        $conn->query( $sqlSequence );
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setup()
    {
        $container = self::$kernel->getContainer();
        $emComp = $container->get('doctrine.orm.competition_entity_manager');
        self::purgeDatabase($emComp);
    }

    /**
     * @param $testSource
     * @return string
     */
    private function commandBuildInterface($testSource)
    {
        $application = new Application('Test competition:interface');
        $command = new CompetitionInterfaceBuild();
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

    public function testCorrect()
    {
        $output=$this->commandBuildInterface('interface-correct.yml');
        $message = 'Commencing at';
        $this->assertContains($message,$output);
    }

}