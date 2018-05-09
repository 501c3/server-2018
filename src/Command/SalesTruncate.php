<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/23/18
 * Time: 3:57 PM
 */

namespace App\Command;

use App\Kernel;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SalesTruncate extends Command
{
    /** @var EntityManagerInterface */
    private $purgerSales;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $kernel = new Kernel('dev', true );
        $kernel->boot();
        $entityManagerSales = $kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        $this->purgerSales=new ORMPurger($entityManagerSales);
        $this->purgerSales->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
    }

    public function configure()
    {
        $this->setName('sales:truncate')
            ->setDescription('Empty the sales channel.')
            ->setHelp('You must specify a valid Yaml file which specifies sales channel and inventory.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $connection=$this->purgerSales->getObjectManager()->getConnection();
        $connection->query( 'SET FOREIGN_KEY_CHECKS=0');
        $this->purgerSales->purge();
        $connection->query( 'SET FOREIGN_KEY_CHECKS=0') ;
        $output->writeln('Completed Sales Truncation.');
    }
}