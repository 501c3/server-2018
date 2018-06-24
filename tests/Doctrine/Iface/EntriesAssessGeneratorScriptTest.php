<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/9/18
 * Time: 10:36 AM
 */

namespace App\Tests\Doctrine\Sales;

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
use App\Repository\Competition\EventRepository;
use App\Repository\Competition\IfaceRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Competition\PlayerRepository;
use App\Repository\Models\ValueRepository;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\ContactRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\Iface\ParticipantRepository;
use App\Repository\Sales\Iface\PlayerRepository as IfacePlayerRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\WorkareaRepository;
use App\Tests\Doctrine\Iface\EntriesAssessGenerator;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class EntriesAssessGeneratorScriptTest extends KernelTestCase
{
    /** @var EntriesAssessGenerator */
    private static $entriesAssessGenerator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param $dataFile
     * @throws DBALException
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
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
     */
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
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $entityManagerCompetition->getRepository(Player::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $entityManagerCompetition->getRepository(Event::class);
       /** @var IfaceRepository $ifaceRepository */
        $ifaceRepository = $entityManagerCompetition->getRepository(Iface::class);


        /** @var Channel $channel */
        $channel=$channelRepository->findOneBy(['name'=>'Georgia DanceSport']);

        $ifaceParticipantRepository = new ParticipantRepository($valueRepository,
                                                                $modelRepository,
                                                                $formRepository,
                                                                $tagRepository);


        $ifacePlayerRepository  = new IfacePlayerRepository($valueRepository,
                                                            $modelRepository,
                                                            $tagRepository,
                                                            $formRepository,
                                                            $competitionRepository,
                                                            $ifaceRepository,
                                                            $playerRepository,
                                                            $eventRepository);
        $ifacePlayerRepository->initClassifier($channel);

        self::$entriesAssessGenerator
            = new EntriesAssessGenerator($channelRepository,
                                         $contactRepository,
                                         $workareaRepository,
                                         $formRepository,
                                         $tagRepository ,
                                         $competitionRepository,
                                         $modelRepository,
                                         $valueRepository,
                                         $ifaceParticipantRepository,
                                         $ifacePlayerRepository);
    }

    public function testConnect()
    {
        $this->assertTrue(true);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not channel" at row:4, col:1 expected "channel".
     * @expectedExceptionCode 4002
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4002ExceptionChannel()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4002-exception-channel.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionCode "Invalid Channel" at row:4, col:10 does not exist.
     * @expectedExceptionCode 4004
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4004ExceptionInvalidChannel()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4004-exception-invalid-channel.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not participation" at row:6, col:1 expected "participation".
     * @expectedExceptionCode 4202
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4202ExceptionParticipation()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4202-exception-participation.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not contact" at row:9, col:9 expected "model","contact","genre","lead","follow","events".
     * @expectedExceptionCode 4212
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4212ExceptionKeysContact()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4212-exception-keys-contact.yml' );
        self::$entriesAssessGenerator->parse($yamlText);

    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not events" at row:15, col:9 expected "model","contact","genre","lead","follow","events".
     * @expectedExceptionCode 4212
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4212ExceptionKeysEvents()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4212-exception-keys-events.yml' );
        self::$entriesAssessGenerator->parse($yamlText);

    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not follow" at row:12, col:9 expected "model","contact","genre","lead","follow","events".
     * @expectedExceptionCode 4212
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4212ExceptionKeysFollow()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4212-exception-keys-follow.yml' );
        self::$entriesAssessGenerator->parse($yamlText);

    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not genre" at row:10, col:9 expected "model","contact","genre","lead","follow","events".
     * @expectedExceptionCode 4212
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4212ExceptionKeysGenre()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4212-exception-keys-genre.yml' );
        self::$entriesAssessGenerator->parse($yamlText);

    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not lead" at row:11, col:9 expected "model","contact","genre","lead","follow","events"
     * @expectedExceptionCode 4212
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4212ExceptionKeysLead()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4212-exception-keys-lead.yml' );
        self::$entriesAssessGenerator->parse($yamlText);

    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not model" at row:8, col:9 expected "model","contact","genre","lead","follow","events".
     * @expectedExceptionCode 4212
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4212ExceptionKeysModel()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4212-exception-keys-model.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_first" at row:9, col:19 expected "first".
     * @expectedExceptionCode 4216
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4216ExceptionFirst()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4216-exception-first.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_last" at row:9, col:56 expected "first".
     * @expectedExceptionCode 4218
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4218ExceptionLast()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4218-exception-last.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_proficiency" at row:11, col:16 expected "proficiency".
     * @expectedExceptionCode 4220
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4220ExceptionProficiency()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4220-exception-proficiency.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_age" at row:11, col:43 expected "age.
     * @expectedExceptionCode 4222
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4222ExceptionAge()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4222-exception-age.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_type" at row:11, col:55 expected "type".
     * @expectedExceptionCode 4224
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4224ExceptionType()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4224-exception-type.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Proficiency" at row:11, col:29 is invalid.
     * @expectedExceptionCode 4226
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4226ExceptionInvalidProficiency()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4226-exception-invalid-proficiency.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Proficiency" at row:13, col:29 is invalid.
     * @expectedExceptionCode 4226
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4226ExceptionInvalidProficiencyFollow()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4226-exception-invalid-proficiency-follow.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Age" at row:11, col:48 is invalid.
     * @expectedExceptionCode 4228
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4228ExceptionInvalidAge()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4228-exception-invalid-age.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Age" at row:14, col:20 is invalid.
     * @expectedExceptionCode 4228
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4228ExceptionInvalidAgeFollow()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4228-exception-invalid-age-follow.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Type" at row:11, col:61 is invalid.
     * @expectedExceptionCode 4230
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4230ExceptionInvalidType()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4230-exception-invalid-type.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not proficiencies" at row:13, col:13 expected "proficiencies".
     * @expectedExceptionCode 4232
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4232ExceptionProficiencies()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4232-exception-proficiencies.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not ages" at row:14, col:13 expected "ages".
     * @expectedExceptionCode 4234
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4234ExceptionAges()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4234-exception-ages.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Model" at row:8, col:16 is invalid.
     * @expectedExceptionCode 4240
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4240ExceptionInvalidModel()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4242-exception-style.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not style" at row:16, col:13 expected "style".
     * @expectedExceptionCode 4242
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4242ExceptionStyle(){
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4242-exception-style.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not type" at row:17, col:13 expected "type".
     * @expectedExceptionCode 4244
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4244ExceptionTypeEvent(){
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4244-exception-type-event.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not tag" at row:18, col:13 expected "tag".
     * @expectedExceptionCode 4246
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4246ExceptionTag()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4246-exception-tag.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not chosen" at row:19, col:13 expected "chosen".
     * @expectedExceptionCode 4248
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4248ExceptionChosen()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4248-exception-chosen.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not assess" at row:20, col:13 expected "assess".
     * @expectedExceptionCode 4250
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4250ExceptionAssess()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4250-exception-assess.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Style" at row:16, col:20 is invalid.
     * @expectedExceptionCode 4260
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4260ExceptionInvalidStyle()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4260-exception-invalid-style.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Type" at row:17, col:19 is invalid.
     * @expectedExceptionCode 4262
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4262ExceptionInvalidTypeEvent()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4262-exception-invalid-type-event.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Tag" at row:18, col:18 is invalid.
     * @expectedExceptionCode 4264
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4264ExceptionInvalidTag()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4264-exception-invalid-tag.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Chosen" at row:19, col:21 expects "one","all".
     * @expectedExceptionCode 4266
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4266ExceptionInvalidChosen()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4266-exception-invalid-chosen.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Invalid Assess" at row:20, col:21 is invalid.
     * @expectedExceptionCode 4268
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test4268ExceptionInvalidAssess()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-4268-exception-invalid-assess.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }


    /**
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testCorrect()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-correct.yml' );
        self::$entriesAssessGenerator->parse($yamlText);
    }
}