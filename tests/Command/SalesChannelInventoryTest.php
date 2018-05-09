<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/24/18
 * Time: 9:58 AM
 */

namespace Tests\Command;

use App\Command\SalesChannel;
use App\Kernel;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Dotenv\Dotenv;

class SalesChannelInventoryTest extends KernelTestCase
{


    /** @var Kernel */
    protected static $kernel;

    /** @var EntityManagerInterface */
    private static $entityManagerSales;

    /** @var EntityManagerInterface */
    private static $entityManagerConfiguration;

    public static function setUpBeforeClass()
    {
        (new Dotenv())->load(__DIR__.'/../../.env');
        // TODO: Change dev->prod, false for production
        self::$kernel = self::bootKernel();
        self::$entityManagerSales = self::$kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        self::$entityManagerConfiguration = self::$kernel->getContainer()->get('doctrine.orm.configuration_entity_manager');
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp()
    {
        /** @var ORMPurger $purgerConfiguration */
        $purgerConfiguration = new ORMPurger(self::$entityManagerConfiguration);
        /** @var ORMPurger $purgerSales */
        $purgerSales = new ORMPurger(self::$entityManagerSales);

        $purgerConfiguration->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purgerSales->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);

        /** @var Connection $connectionSales */
        $connectionSales=$purgerSales->getObjectManager()->getConnection();
        /** @var Connection $connectionConfiguration */
        $connectionConfiguration=$purgerConfiguration->getObjectManager()->getConnection();

        $connectionSales->query( 'SET FOREIGN_KEY_CHECKS=0' );
        $connectionConfiguration->query('SET FOREIGN_KEY_CHECKS=0');
        $purgerSales->purge();
        $purgerConfiguration->purge();
        $connectionSales->query('SET FOREIGN_KEY_CHECKS=1');
        $connectionConfiguration->query('SET FOREIGN_KEY_CHECKS=1');
    }

    private function loadCommandTestSource(SalesChannel $command, string $testSource)
    {
        $application = new Application('Test SalesChannelInventory');
        $application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'=>$command->getName(),
            'filename'=>__DIR__."/../Scripts/Yaml/Sales/$testSource"
        ]);
        $output=$commandTester->getDisplay();
        return $output;
    }


    public function testCommandSalesExceptionChannelInventory()
    {
        $output=$this->loadCommandTestSource(new SalesChannel(), 'sales-2202-exception-logo.yml');
        $this->assertContains('"/home/mgarber/Dev2018/server/badlocation/images/dancers-icon.png" at row:6, col:7 not found.',$output);
    }

    public function testCommandSalesChannelInventory()
    {
        $output=$this->loadCommandTestSource(new SalesChannel(), 'sales-correct.yml');
        $this->assertContains('Completed at',$output);
    }

}