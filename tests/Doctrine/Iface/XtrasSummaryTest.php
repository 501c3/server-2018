<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 7/16/18
 * Time: 10:44 PM
 */

namespace App\Tests\Doctrine\Iface;


use App\Entity\Competition\Competition;
use App\Entity\Competition\Event;
use App\Entity\Competition\Iface;
use App\Entity\Competition\Model;
use App\Entity\Competition\Player;
use App\Entity\Models\Value;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Contact;
use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Sales\ContactRepository;
use App\Repository\Sales\Iface\ParticipantRepository;
use App\Repository\Sales\Iface\PlayerRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\WorkareaRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class XtrasSummaryTest  extends KernelTestCase
{
    /** @var WorkareaRepository */
    private static $workareaRepository;

    /** @var ContactRepository */
    private static $contactRepository;

    /** @var TagRepository */
    private static $tagRepository;

    /** @var ParticipantPoolGenerator */
    private static $participantPoolGenerator;

    /** @var PlayerRepository */
    private static $ifacePlayerRepository;

    /** @var ParticipantRepository */
    private static $participantRepository;

    /** @var ModelRepository */
    private static $modelRepository;


    /** @var Channel */
    private static $channel;


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

    /**
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function setUpBeforeClass()
    {
        (new Dotenv())->load( __DIR__ . '/../../../.env' );
        $kernel = self::bootKernel();
        $entityManagerModels = $kernel->getContainer()->get( 'doctrine.orm.models_entity_manager' );
        $entityManagerCompetition = $kernel->getContainer()->get('doctrine.orm.competition_entity_manager');
        $entityManagerSales = $kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        self::initializeDatabase($entityManagerModels,'models.sql');
        self::initializeDatabase($entityManagerCompetition,'competition-interface.sql');
        self::initializeDatabase($entityManagerSales,'sales-channel.sql');
        /** @var CompetitionRepository $competitionRepository */
        $competitionRepository=$entityManagerCompetition->getRepository(Competition::class);
        $modelRepository=$entityManagerCompetition->getRepository(Model::class);
        $ifaceRepository=$entityManagerCompetition->getRepository(Iface::class);
        $playerRepository = $entityManagerCompetition->getRepository( Player::class);
        $eventRepository = $entityManagerCompetition->getRepository(Event::class);
        $valueRepository=$entityManagerModels->getRepository(Value::class);
        $channelRepository = $entityManagerSales->getRepository(Channel::class);
        $formRepository=$entityManagerSales->getRepository(Form::class);
        $tagRepository = $entityManagerSales->getRepository(Tag::class);
        self::$workareaRepository= $entityManagerSales->getRepository(Workarea::class);
        self::$contactRepository = $entityManagerSales->getRepository(Contact::class);
        self::$contactRepository= $entityManagerSales->getRepository(Workarea::class);
        self::$tagRepository = $entityManagerSales->getRepository(Tag::class);
        /** @var ParticipantPoolGenerator */
        self::$participantPoolGenerator = new ParticipantPoolGenerator($competitionRepository,
                                                                        $modelRepository,
                                                                        $ifaceRepository,
                                                                        $valueRepository);
        self::$participantRepository = new ParticipantRepository($modelRepository,
                                                                $formRepository,
                                                                self::$tagRepository);
        self::$ifacePlayerRepository = new PlayerRepository($valueRepository,
            $modelRepository,
            $tagRepository,
            $formRepository,
            $competitionRepository,
            $ifaceRepository,
            $playerRepository,
            $eventRepository);
        self::$channel = $channelRepository->find(1);

        self::$ifacePlayerRepository->initClassifier(self::$channel,true);

        $fileLocation = realpath( __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/participant-pool.yml' );
        $yamlText =  file_get_contents($fileLocation);
        self::$participantPoolGenerator->parse($yamlText);
        self::$modelRepository=$modelRepository;
    }

}