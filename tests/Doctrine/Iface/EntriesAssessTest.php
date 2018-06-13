<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/9/18
 * Time: 9:39 PM
 */

namespace App\Tests\Doctrine\Iface;
use App\Entity\Competition\Competition;
use App\Entity\Competition\Model;
use App\Entity\Models\Value;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Contact;
use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Models\ValueRepository;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\ContactRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\WorkareaRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class EntriesAssessTest extends KernelTestCase
{
    /** @var EntriesAssessGenerator */
    private static $entriesAssessGenerator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param $dataFile
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function initializeDatabase(EntityManagerInterface $entityManager, $dataFile)
    {
        $purger = new ORMPurger($entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $conn = $purger->getObjectManager()->getConnection();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
        $sql = file_get_contents( __DIR__ . '/../../Scripts/SQL/' .$dataFile );
        $conn->query( $sql );
    }

    public static function setUpBeforeClass()
    {
        (new Dotenv())->load( __DIR__ . '/../../../.env' );
        $kernel=self::bootKernel();
        $entityManagerCompetition = $kernel->getContainer()->get('doctrine.orm.competition_entity_manager');
        $entityManagerModels = $kernel->getContainer()->get('doctrine.orm.models_entity_manager');
        $entityManagerSales = $kernel->getContainer()->get( 'doctrine.orm.sales_entity_manager');
        self::initializeDatabase($entityManagerCompetition,'competition-interface.sql');
        self::initializeDatabase($entityManagerModels,'models.sql');
        self::initializeDatabase($entityManagerSales,'sales-channel.sql');
        /** @var ChannelRepository $channelRepository */
        $channelRepository = $entityManagerSales->getRepository(Channel::class);
        /** @var ContactRepository $contactRepository */
        $contactRepository = $entityManagerSales->getRepository(Contact::class);
        /** @var WorkareaRepository $workareaRepository */
        $workareaRepository = $entityManagerSales->getRepository(Workarea::class);
        /** @var FormRepository $formRepository */
        $formRepository = $entityManagerSales->getRepository(Form::class);
        /** @var TagRepository $tagRepository */
        $tagRepository = $entityManagerSales->getRepository(Tag::class);
        /** @var CompetitionRepository $competitionRepository */
        $competitionRepository = $entityManagerCompetition->getRepository(Competition::class);
        /** @var ModelRepository $modelRepository */
        $modelRepository = $entityManagerCompetition->getRepository(Model::class);
        /** @var ValueRepository $valueRepository */
        $valueRepository = $entityManagerModels->getRepository(Value::class);
        self::$entriesAssessGenerator
            = new EntriesAssessGenerator($channelRepository,
                                        $contactRepository,
                                        $workareaRepository,
                                        $formRepository,
                                        $tagRepository ,
                                        $competitionRepository,
                                        $modelRepository,
                                        $valueRepository);

    }



}