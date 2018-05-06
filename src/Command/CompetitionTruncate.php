<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/17/18
 * Time: 10:35 AM
 */

namespace App\Command;


use App\Kernel;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompetitionTruncate extends Command
{
    private $entityManager;
    public function __construct(?string $name = null)
    {
        parent::__construct( $name );
        $kernel = new Kernel( 'dev', true );
        $kernel->boot();
        $this->entityManager = $kernel->getContainer()->get( 'doctrine.orm.competition_entity_manager' );
    }

    /**
     * @param array $excludedTables
     * @throws \Doctrine\DBAL\DBALException
     */
    private function purge(array $excludedTables=[])
    {
        $purger = new ORMPurger($this->entityManager, $excludedTables);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $connection=$purger->getObjectManager()->getConnection();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function configure(){
        $this->setName('competition:truncate')
            ->setDescription('Truncate the competition database.')
            ->setHelp('Truncate the competition database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->purge();
            $output->writeln( 'Truncate complete.' );
        } catch (\Exception $e) {
            $output->writeln( $e->getMessage() );
        }
    }
}