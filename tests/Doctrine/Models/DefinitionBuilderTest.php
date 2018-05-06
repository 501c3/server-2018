<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/5/18
 * Time: 11:04 AM
 */


namespace App\Tests\Doctrine\Models;
use App\Doctrine\Model\DefinitionBuilder;
use App\Doctrine\Model\PrimitivesBuilder;
use App\Entity\Configuration\Miscellaneous;
use App\Entity\Configuration\Model as Configuration;
use App\Entity\Models\Domain;
use App\Entity\Models\Event;
use App\Entity\Models\Model;
use App\Entity\Models\Player;
use App\Entity\Models\Subevent;
use App\Entity\Models\Tag;
use App\Entity\Models\Value;
use App\Repository\Configuration\MiscellaneousRepository;
use App\Repository\Configuration\ModelRepository as ConfigurationRepository;
use App\Repository\Models\DomainRepository;
use App\Repository\Models\EventRepository;
use App\Repository\Models\MappingRepository;
use App\Repository\Models\ModelRepository;
use App\Repository\Models\PlayerRepository;
use App\Repository\Models\SubeventRepository;
use App\Repository\Models\TagRepository;
use App\Repository\Models\ValueRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class ModelBuilderTest
 * @package App\Tests\Doctrine\Models
 *
 */

/**
 * Class DefinitionBuilderTest
 * @package App\Tests\Doctrine\Models
 */
class DefinitionBuilderTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private static $entityManagerModels;
    /** @var EntityManagerInterface */
    private static $entityManagerConfiguration;
    /** @var DefinitionBuilder */
    private $definitionBuilder;

    public static function setUpBeforeClass()
    {
        (new Dotenv())->load( __DIR__ . '/../../../.env' );
        $kernel = self::bootKernel();
        self::$entityManagerModels = $kernel->getContainer()->get( 'doctrine.orm.models_entity_manager' );
        self::$entityManagerConfiguration = $kernel->getContainer()->get( 'doctrine.orm.configuration_entity_manager' );
    }


    /**
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\PrimitivesException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    protected function setUp()
    {
        $purgerModels = new ORMPurger( self::$entityManagerModels );
        $purgerConfiguration = new ORMPurger( self::$entityManagerConfiguration );
        $purgerModels->setPurgeMode( ORMPurger::PURGE_MODE_TRUNCATE );
        $purgerConfiguration->setPurgeMode( ORMPurger::PURGE_MODE_TRUNCATE );
        $connectionModel = $purgerModels->getObjectManager()->getConnection();
        /** @var \Doctrine\DBAL\Connection $connectionConfiguration */
        $connectionConfiguration = $purgerConfiguration->getObjectManager()->getConnection();
        $connectionModel->query( 'SET FOREIGN_KEY_CHECKS=0' );
        $connectionConfiguration->query( 'SET FOREIGN_KEY_CHECKS=0' );
        $purgerModels->purge();
        $purgerConfiguration->purge();
        $connectionModel->query( 'SET FOREIGN_KEY_CHECKS=1' );
        $connectionConfiguration->query( 'SET FOREIGN_KEY_CHECKS=1' );

        /** @var DomainRepository $domainRepository */
        /** @var ValueRepository $valueRepository */
        /** @var TagRepository $tagRepository */
        /** @var MappingRepository $mappingRepository */
        /** @var MiscellaneousRepository $miscellaneousRepository */
        $domainRepository = self::$entityManagerModels->getRepository( Domain::class );
        $valueRepository = self::$entityManagerModels->getRepository( Value::class );
        $tagRepository = self::$entityManagerModels->getRepository( Tag::class );
       // $mappingRepository = self::$entityManagerModels->getRepository( Mapping::class );
        $valueRepository->getEntityManager()->clear();
        $miscellaneousRepository = self::$entityManagerConfiguration->getRepository( Miscellaneous::class );
        $primitivesBuilder = new PrimitivesBuilder( $domainRepository,
            $valueRepository,
            $tagRepository,
            $miscellaneousRepository );
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/primitives.yml' );
        $primitivesBuilder->build( $yamlTxt );

        /** @var ModelRepository $modelRepository */
        $modelRepository = self::$entityManagerModels->getRepository( Model::class );
        $modelRepository->getEntityManager()->clear();
        /** @var  EventRepository $eventRepository */
        $eventRepository = self::$entityManagerModels->getRepository( Event::class );
        /** @var  SubeventRepository $subeventRepository */
        $subeventRepository = self::$entityManagerModels->getRepository( Subevent::class );
        /** @var PlayerRepository $playerRepository */
        $playerRepository = self::$entityManagerModels->getRepository( Player::class );
        /* @var ChoiceRepository $choiceRepository */
        //$choiceRepository = self::$entityManagerModels->getRepository( Choice::class );
        /** @var ConfigurationRepository $configurationRepository */
        $configurationRepository = self::$entityManagerConfiguration->getRepository( Configuration::class );
        $valueRepository = self::$entityManagerModels->getRepository( Value::class );
        //TODO Delete commented out depositories when fully tested.
        $this->definitionBuilder = new DefinitionBuilder(
            $modelRepository,
            $domainRepository,
            $eventRepository,
            $subeventRepository,
            $playerRepository,
            $tagRepository,
            $valueRepository,
            $configurationRepository );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid" at row:1, col:1 expected "model".
     * @expectedExceptionCode 1102
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1102ExceptionModel()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1102-exception-model.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_domain" at row:5, col:1 expected "domains".
     * @expectedExceptionCode 1202
     *
     * ModelExceptionCode::DOMAINS = 1202
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1202ExceptionDomains()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1202-exception-domains.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }


    /**
     * @expectedException \App\Exceptions\MissingException
     * @expectedExceptionMessage Missing "substyle","age" domain definitions between lines 6 and 24
     * @expectedExceptionCode 1203
     *
     * ModelExceptionCode::MISSING_DOMAINS = 1203
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1203ExceptionMissingDomain()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1203-exception-missing-domain.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_domain" at row:16, col:5 expected "style","substyle","proficiency","age","type","tag".
     * @expectedExceptionCode 1204
     *
     * ModelExceptionCode::DOMAIN = 1204
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1204ExceptionDomain()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1204-exception-domain.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expecteedExceptionMessage "no_value" at row:12, col:11 is not a valid substyle.
     * @expectedExceptionCode 1206
     *
     * ModelExceptionCode::VALUE = 1206
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1206ExceptionValue()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1206-exception-value.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expecteedExceptionMessage "no_dances" at row:49, col:1 expected "dances".
     * @expectedExceptionCode 1302
     *
     * ModelExceptionCode::DANCES = 1302
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1302ExceptionDances()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1302-exception-dances.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_style" at row:52, col:5 is an invalid style.
     * @expectedExceptionCode 1304
     *
     * ModelExceptionCode::STYLE = 1304
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1304ExceptionStyle()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1304-exception-style.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_substyle" at row:53, col:9 which is an invalid substyle.
     * @expectedExceptionCode 1306
     *
     * ModelExceptionCode::SUBSTYLE
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1306ExceptionSubstyle()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1306-exception-substyle.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_dance" at row:54, col:25 is an invalid dance.
     * @expectedExceptionCode 1308
     *
     * ModelExceptionCode::DANCE = 1308
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */

    public function test1308ExceptionDance()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1308-exception-dance.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_players" at row:60, col:1 expected "players".
     * @expectedExceptionCode 1402
     *
     * ModelExceptionCode::PLAYERS = 1402
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1402ExceptionPlayers()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1402-exception-players.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid_key" at row:68, col:7 expected "substyle","proficiency","age","type".
     * @expectedExceptionCode 1404
     *
     * ModelExceptionCode::INVALID_DOMAIN_KEY = 1404
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1404ExceptionInvalidDomainKey()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1404-exception-invalid-domain-key.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }


    /**
     * @expectedException  \App\Exceptions\MissingException
     * @expectedExceptionMessage Missing "proficiency","type" domain definitions between lines 62 and 68.
     * @expectedExceptionCode 1406
     *
     * ModelExceptionCode::MISSING_KEYS = 1406
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1406ExceptionMissingKeys()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1406-exception-missing-keys.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }


    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid_component" at row:98, col:13 is not a valid age.
     * @expectedExceptionCode 1408
     *
     * ModelExceptionCode::INVALID_COMPONENT = 1408
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1408ExceptionInvalidComponent()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1408-exception-invalid-component.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\RedundanceException
     * @expectedExceptionMessage Player redundancy for Rhythm, Newcomer, Baby, Amateur, previous lines: 63,69,77,91, current lines: 94,96,98,100.
     * @expectedExceptionCode 1410
     *
     * ModelExceptionCode::REDUNDANT_COMPONENT = 1410
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException=
     */
    public function test1410ExceptionRedundantPlayers()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1410-exception-redundant-component.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_event_tags" at row:92, col:1 expected "event-tags".
     * @expectedExceptionCode 1502
     *
     * ModelExceptionCode::EVENT_TAGS = 1502
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1502ExceptionEventTags()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1502-exception-event-tags.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid_tag" at row:94, col:7 is not valid.
     * @expectedExceptionCode 1504
     *
     * ModelExceptionCode::INVALID_EVENT_TAG = 1504
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1504InvalidEventTag()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1504-exception-event-tag.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage  "no_collection" at row:95, col:1 expected "event-collections".
     * @expectedExceptionCode 1602
     *
     * ModelExceptionCode::EVENT_COLLECTIONS = 1602
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1602ExceptionEventCollection()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1602-exception-event-collections.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "invalid_key" at row:99, col:9 expected "style","age","proficiency","event-tag","type".
     * @expectedExceptionCode 1604
     *
     * ModelExceptionCode::INVALID_KEY = 1604
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1604ExceptionInvalidKey()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1604-exception-invalid-key.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\MissingException
     * @expectedExceptionMessage Missing "style" domain definitions between lines 98 and 154.
     * @expectedExceptionCode 1606
     *
     * ModelExceptionCode::MISSING_KEY = 1606
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1606ExceptionMissingKey()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1606-exception-missing-key.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_style" at row:99, col:16 is invalid.
     * @expectedExceptionCode 1608
     *
     * ModelExceptionCode::INVALID_STYLE
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1608ExceptionInvalidStyle()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1608-exception-invalid-style.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_event_tag" at row:100, col:20 is invalid.
     * @expectedExceptionCode 1610
     *
     * ModelExceptionCode::EVENT_TAG = 1610
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1610ExceptionEventTag()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1610-exception-event-tag.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_events" at row:119, col:21 expected "events" or "single-event".
     * @expectedExceptionCode 1612
     *
     * ModelExceptionCode::EVENTS = 1612
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1612ExceptionEvents()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1612-exception-events.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_eligible" at row:118, col:21 expected "eligible".
     * @expectedExceptionCode 1614
     *
     * ModelExceptionCode::ELIGIBLE = 1614
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
 */
    public function test1614EventsEligibleKey()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1614-exception-eligible.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_tag" at row:116, col:13 is invalid tag.
     * @expectedExceptionCode 1616
     *
     * ModelExceptionCode::INVALID_TAG = 1616
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */

    public function test1616ExceptionInvalidTag()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1616-exception-invalid-tag.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }


    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_proficiency" at row:117, col:17 is invalid proficiency.
     * @expectedExceptionCode 1618
     *
     * ModelExceptionCode::INVALID_PROFICIENCY = 1618
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
 */

    public function test1618ExceptionInvalidProficiency()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1618-exception-invalid-proficiency.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_age" at row:102, col:13 is an invalid age.
     * @expectedExceptionCode 1620
     *
     * ModelExceptionCode::INVALID_AGE = 1620
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
 */
    public function test1620ExceptionInvalidAge()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1620-exception-invalid-age.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_type" at row:157, col:13 is invalid type.
     * @expectedExceptionCode 1622
     *
     * ModelExceptionCode::TYPE_INVALID = 1622
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
 */
    public function test1622ExceptionInvalidType()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1622-exception-invalid-type.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Foxtrot" at row:113, col:46 expected single bracket "[" before token..
     * @expectedExceptionCode 1624
     *
     * ModelExceptionCode::SINGLE_BRACKET = 1624
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1624ExceptionSingleBracket()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1624-exception-single-bracket.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage  "not_substyle" at row:136, col:36 is invalid substyle.
     * @expectedExceptionCode 1626
     *
     * ModelExceptionCode::INVALID_SUBSTYLE = 1626
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1626ExceptionInvalidSubstyle()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1626-exception-invalid-substyle.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }


    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "not_dance" at row:158, col:45 is an invalid dance.
     * @expectedExceptionCode \App\Exceptions\ModelExceptionCode::INVALID_DANCE
     *
     * ModelExceptionCode::INVALID_DANCE = 1628
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1628ExceptionInvalidDance()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1628-exception-invalid-dance.yml' );
        $this->definitionBuilder->build( $yamlTxt );
    }


    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "Waltz" at row:160, col:34 there must be "[[" and matched brackets.
     * @expectedExceptionCode 1634
     *
     * ModelExceptionCode::DOUBLE_BRACKET = 1634
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function test1634ExceptionDoubleBracket()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-1634-exception-double-bracket.yml');
        $this->definitionBuilder->build( $yamlTxt );
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\MissingException
     * @throws \App\Exceptions\GeneralException
     * @throws \App\Exceptions\RedundanceException
     */
    public function testLoadCorrect()
    {
        $yamlTxt = file_get_contents( __DIR__ . '/../../Scripts/Yaml/Model/model-correct-amateur.yml');
        $this->definitionBuilder->build( $yamlTxt );
        $this->assertTrue(true);
    }
}