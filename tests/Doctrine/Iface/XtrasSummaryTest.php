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
use App\Entity\Sales\Iface\Participant;
use App\Entity\Sales\Iface\Player as IfacePlayer;
use App\Entity\Sales\Iface\Summary;
use App\Entity\Sales\Inventory;
use App\Entity\Sales\Pricing;
use App\Entity\Sales\Receipts;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use App\Exceptions\ClassifyException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Sales\ContactRepository;
use App\Repository\Sales\Iface\ParticipantRepository;
use App\Repository\Sales\Iface\PlayerRepository;
use App\Repository\Sales\Iface\SummaryRepository;
use App\Repository\Sales\Iface\XtrasRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\WorkareaRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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

    /** @var SummaryRepository */
    private static $summaryRepository;

    /** @var ParticipantRepository */
    private static $participantRepository;

    /** @var ModelRepository */
    private static $modelRepository;

    /** @var XtrasRepository */
    private static $xtraRepository;

    /** @var Channel */
    private static $channel;


    /**
     * @param string $first
     * @param string $last
     * @return Contact
     * @throws ORMException
     * @throws OptimisticLockException
     */

    public function generateContact(
        string $first,
        string $last):Contact
    {
        $tag = self::$tagRepository->fetch('competition');
        $workarea = self::$workareaRepository->fetch(self::$channel,$tag);
        $contact=new Contact();
        $contact->setFirst(substr($first,0,40))
            ->setLast(substr($last,0,40))
            ->setCity('Sandy Springs')
            ->setSt('GA')
            ->setCountry('United States')
            ->setPhone('(678)235-8395')
            ->setOrganization("Organization")
            ->setEmail('mgarber@georgiasport.org');
        $contact->getWorkarea()->add($workarea);
        $em=self::$contactRepository->getEntityManager();
        $em->persist($contact);
        $em->flush();
        return $contact;
    }


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
        $inventoryRepository = $entityManagerSales->getRepository(Inventory::class);
        $pricingRepository = $entityManagerSales->getRepository(Pricing::class);
        $receiptsRepository = $entityManagerSales->getRepository(Receipts::class);
        self::$workareaRepository= $entityManagerSales->getRepository(Workarea::class);
        self::$contactRepository = $entityManagerSales->getRepository(Contact::class);
        self::$tagRepository = $entityManagerSales->getRepository(Tag::class);

        /** @var ParticipantPoolGenerator */
        self::$participantPoolGenerator = new ParticipantPoolGenerator($competitionRepository,
                                                                        $modelRepository,
                                                                        $ifaceRepository,
                                                                        $valueRepository);
        self::$summaryRepository = new SummaryRepository($channelRepository,
                                                    $formRepository,
                                                    self::$tagRepository,
                                                    $inventoryRepository,
                                                    $pricingRepository,
                                                    $receiptsRepository);
        self::$participantRepository = new ParticipantRepository($modelRepository,
                                                                $formRepository,
                                                                self::$tagRepository,
                                                                self::$summaryRepository);
        self::$ifacePlayerRepository = new PlayerRepository($valueRepository,
                                                            $modelRepository,
                                                            $tagRepository,
                                                            $formRepository,
                                                            $competitionRepository,
                                                            $ifaceRepository,
                                                            $playerRepository,
                                                            $eventRepository,
                                                            self::$summaryRepository);
        self::$xtraRepository = new XtrasRepository($channelRepository,
                                                   $formRepository,
                                                   $tagRepository,
                                                   $inventoryRepository,
                                                   $pricingRepository,
                                                   self::$summaryRepository);

        self::$channel = $channelRepository->find(1);

        self::$ifacePlayerRepository->initClassifier(self::$channel,true);

        $fileLocation = realpath( __DIR__ . '/../../Scripts/Yaml/Iface/ParticipantPool/participant-pool.yml' );
        $yamlText =  file_get_contents($fileLocation);
        self::$participantPoolGenerator->parse($yamlText);
        self::$modelRepository=$modelRepository;
    }


    /**
     * @param string $genre
     * @param string $maleProficiency
     * @param string $femaleProficiency
     * @param int $maleAge
     * @param int $femaleAge
     * @return array
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ClassifyException
     * @throws \Exception
     */
    private function generateAmateurCouple(
        string $genre,
        string $maleProficiency,
        string $femaleProficiency,
        int $maleAge,
        int $femaleAge) : array
    {
        /** @var Contact $contact */
        $contact = self::generateContact( 'GADS', 'Select events' );
        $workarea = $contact->getWorkarea()->first();
        /** @var ParticipantPoolGenerator $gen */
        $gen = self::$participantPoolGenerator;
        /** @var Participant $gent */
        $gent = $gen->getStudent( 'amateur', 'M', $genre, $maleProficiency, $maleAge );
        /** @var Participant $lady */
        $lady = $gen->getStudent( 'amateur', 'F', $genre, $femaleProficiency, $femaleAge );
        self::$participantRepository->save( $workarea, $gent );
        self::$participantRepository->save( $workarea, $lady );
        $player = self::$ifacePlayerRepository->create( $workarea, $gent->getId(), $lady->getId());
        return [$player,$workarea];
    }

    /**
     * @param string $genre
     * @param string $maleProficiency
     * @param string $female1Proficiency
     * @param string $female2Proficiency
     * @param int $maleAge
     * @param int $femaleAge
     * @return array
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    private function generateProAmCoupleTrio(
        string $genre,
        string $maleProficiency,
        string $female1Proficiency,
        string $female2Proficiency,
        int $maleAge,
        int $femaleAge)
    {
        /** @var Contact $contact */
        $contact = self::generateContact( 'GADS', 'Select events' );
        $workarea = $contact->getWorkarea()->first();
        /** @var ParticipantPoolGenerator $gen */
        $gen = self::$participantPoolGenerator;
        /** @var Participant $gent */
        $gent = $gen->getProfessionalTeacher( 'medal-proam', 'M', $genre, $maleProficiency,$maleAge );
        /** @var Participant $lady1 */
        $lady1 = $gen->getStudent( 'amateur-proam', 'F', $genre, $female1Proficiency, $femaleAge );
        /** @var Participant $lady2 */
        $lady2 = $gen->getStudent( 'amateur-proam', 'F', $genre, $female2Proficiency, $femaleAge );
        self::$participantRepository->save( $workarea, $gent );
        self::$participantRepository->save( $workarea, $lady1 );
        self::$participantRepository->save( $workarea, $lady2 );
        $player1 = self::$ifacePlayerRepository->create( $workarea, $gent->getId(), $lady1->getId());
        $player2 = self::$ifacePlayerRepository->create( $workarea, $gent->getId(), $lady2->getId());
        return [$player1,$player2,$workarea];
    }




    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function testAddPlayerEvents()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport Amateur']);
        $modelId = $model->getId();
        list($player,) = $this->generateAmateurCouple('Standard',
                                                     'Silver',
                                                    'Silver',
                                                           55,
                                                          50);
        $events = $player->getEvents();
        $keys = array_keys($events[$modelId]);
        $player->setSelections([$modelId=>$keys]);
        self::$ifacePlayerRepository->save($player);
        $retrievedPlayer = self::$ifacePlayerRepository->read($player->getId());
        $selections = $retrievedPlayer->getSelections();
        $this->assertArraySubset([$modelId=>$keys],$selections);
    }


    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testPlayerSummaryAdd()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport ProAm']);
        $modelId = $model->getId();
        /**
         * @var IfacePlayer $firstPlayer
         * @var IfacePlayer $secondPlayer
         */
        list($firstPlayer,$secondPlayer,)
            = $this->generateProAmCoupleTrio(
                                'Standard',
                                'Professional',
                                'Intermediate Silver',
                                'Full Silver',30,
                                50);
        $firstEvents = $firstPlayer->getEvents();
        $firstKeys = array_keys($firstEvents[$modelId]);
        $firstPlayer->setSelections([$modelId=>$firstKeys]);
        self::$ifacePlayerRepository->save($firstPlayer);
        $summary = new Summary('USD');
        $summary->add($firstPlayer);
        $noConflicts = $summary->eventConflicts($firstPlayer);
        $this->assertEquals([],$noConflicts);
        $conflicts = $summary->eventConflicts($secondPlayer);
        $secondPlayer->setExclusions($conflicts);
        self::$ifacePlayerRepository->save($secondPlayer);
        $playerOne = self::$ifacePlayerRepository->read($firstPlayer->getId());
        $playerTwo = self::$ifacePlayerRepository->read($secondPlayer->getId());
        $this->assertEquals($firstPlayer->getSelections(), $playerOne->getSelections());
        $this->assertEquals($secondPlayer->getExclusions(), $playerTwo->getExclusions());

    }


    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testPlayerSummaryExclusions()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport ProAm']);
        $modelId = $model->getId();
        /**
         * @var IfacePlayer $firstPlayer
         * @var IfacePlayer $secondPlayer
         */
        list($firstPlayer,$secondPlayer,)
            = $this->generateProAmCoupleTrio(
                'Standard',
                'Professional',
                'Intermediate Silver',
                'Full Silver',30,
                50);
        $firstEvents = $firstPlayer->getEvents();
        $firstKeys = array_keys($firstEvents[$modelId]);
        $firstPlayer->setSelections([$modelId=>$firstKeys]);
        self::$ifacePlayerRepository->save($firstPlayer);

        $summary = new Summary('USD');
        $summary->add($firstPlayer);
        $exclusions= $summary->eventConflicts($secondPlayer);
        $secondPlayer->setExclusions($exclusions);

        $secondEvents = $secondPlayer->getEvents();
        $secondKeys = array_keys($secondEvents[$modelId]);
        $secondPlayer->setSelections([$modelId=>$secondKeys]);
        self::$ifacePlayerRepository->save($secondPlayer);

        /**
         * Retrieve from database and make sure exclusions and selections took.
         */
        $playerOne = self::$ifacePlayerRepository->read($firstPlayer->getId());
        $this->assertEquals($playerOne->getSelections(),[$modelId=>$firstKeys]);
        $playerTwo = self::$ifacePlayerRepository->read($secondPlayer->getId());
        $this->assertEquals($playerTwo->getSelections(),[$modelId=>$secondKeys]);

        foreach($playerTwo->getExclusions() as $modelId=>$exclusions) {
            $selections = $playerOne->getSelections();
            $this->assertGreaterThan(count($exclusions),count($selections[$modelId]));
        }
    }

    /**
     * @param int $ageAmateur
     * @return Summary
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function buildTestSummaryProAm($ageAmateur = 50):Summary
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport ProAm']);
        $modelId = $model->getId();
        /**
         * @var IfacePlayer $firstPlayer
         * @var IfacePlayer $secondPlayer
         */
        list($firstPlayer,$secondPlayer,)
            = $this->generateProAmCoupleTrio(
            'Standard',
            'Professional',
            'Intermediate Silver',
            'Full Silver',30,
            $ageAmateur);
        $firstEvents = $firstPlayer->getEvents();
        $secondEvents = $secondPlayer->getEvents();
        $firstKeys = array_keys($firstEvents[$modelId]);
        $secondKeys = array_keys($secondEvents[$modelId]);
        $firstPlayer->setSelections([$modelId=>$firstKeys]);
        self::$ifacePlayerRepository->save($firstPlayer);
        $summary = new Summary('USD');
        $summary->add($firstPlayer);
        $conflicts=$summary->eventConflicts($secondPlayer);
        $availableSecondKeys=array_diff($secondKeys,$conflicts[$modelId]);
        $secondPlayer->setSelections([$modelId=>$availableSecondKeys]);
        $secondPlayer->setExclusions($conflicts);
        self::$ifacePlayerRepository->save($secondPlayer);
        $summary->add($secondPlayer);
        return $summary;
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testSummaryDescribe()
    {
        $summary = $this->buildTestSummaryProAm();
        $describe = $summary->describe();
        foreach($describe as $name=>$modelIdEvents){
            foreach($modelIdEvents as $modelId=>$events) {
                foreach($events as $id=>$event){
                    $this->assertArrayHasKey('event',$event);
                    $this->assertArrayHasKey('with',$event);
                    $description = $event['event'];
                    $this->assertArrayHasKey('style',$description);
                    $this->assertArrayHasKey('proficiency',$description);
                    $this->assertArrayHasKey('age',$description);
                    $this->assertArrayHasKey('tag',$description);
                    $this->assertArrayHasKey('dances',$description);
                }
            }
        }
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function testSaveRecallSummary()
    {
        $summary = $this->buildTestSummaryProAm();
        $participants=$summary->getParticipants();
        $participantIds = array_keys($participants);
        $id0 = $participantIds[0];
        /** @var Form $form */
        $form=self::$participantRepository->getForm($id0);
        $workarea = $form->getWorkarea();
        self::$summaryRepository->save($workarea,$summary);
        $recalledSummary = self::$summaryRepository->read($workarea);
        $this->assertEquals($summary->describe(),$recalledSummary->describe());
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testSaveRecallXtras()
    {
        $summary = $this->buildTestSummaryProAm();
        $participants=$summary->getParticipants();
        $participantIds = array_keys($participants);
        $id0 = $participantIds[0];
        /** @var Form $form */
        $form=self::$participantRepository->getForm($id0);
        $workarea = $form->getWorkarea();
        $xtras = self::$xtraRepository->fetch($workarea);
        $inventory = $xtras->getInventory();
        foreach(array_keys($inventory) as $id){
            $xtras->setOrder($id,1);
        }
        self::$xtraRepository->save($xtras);
        $recalledXtra = self::$xtraRepository->fetch($workarea);
        $this->assertEquals($xtras->toArray(),$recalledXtra->toArray());
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     */

    public function testSummaryPricingChild()
    {
        /** @var Model $model */
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport Amateur']);
        $modelId = $model->getId();
        list($amateurPlayersYouth,$workarea) =  $this->generateAmateurCouple(
                                                    'Standard',
                                                    'Bronze','Bronze',
                                                    7,7);
        $events=$amateurPlayersYouth->getEvents();
        $keys = array_keys($events[$modelId]);
        $amateurPlayersYouth->setSelections([$modelId=>$keys]);
        $summary=self::$summaryRepository->read($workarea);
        $summary->add($amateurPlayersYouth);
        $assessment=$summary->assess();
        $this->assertEquals(['comp'=>[3=>['dances'=>12,'charge'=>84]],'exam'=>[]],$assessment);
        self::$summaryRepository->save($workarea,$summary);
        $recalledSummary = self::$summaryRepository->read($workarea);
        $recalledAssessment = $recalledSummary->assess();
        $this->assertEquals($recalledAssessment,$assessment);
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testSummaryPricingAdult()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport ProAm']);
        $modelId = $model->getId();
        list($fullGoldPlayer,$preBronzePlayer,$workarea) =  $this->generateProAmCoupleTrio(
            'Standard',
            'Professional',
            'Full Gold',
            'Pre Bronze',
            30,40);
        $goldEvents=$fullGoldPlayer->getEvents();
        $goldKeys = array_keys($goldEvents[$modelId]);
        $fullGoldPlayer->setSelections([$modelId=>$goldKeys]);
        $bronzeEvents = $preBronzePlayer->getEvents();
        $bronzeKeys = array_keys($bronzeEvents[$modelId]);
        $preBronzePlayer->setSelections([$modelId=>$bronzeKeys]);
        $summary=self::$summaryRepository->read($workarea);
        $summary->add($fullGoldPlayer);
        $summary->add($preBronzePlayer);
        $assessment=$summary->assess();
        $expected = ['comp'=>[4=>['dances'=>18,'charge'=>216],5=>['dances'=>12,'charge'=>144]],'exam'=>[]];
        $this->assertEquals($expected,$assessment);
        self::$summaryRepository->save($workarea,$summary);
        $recalledSummary = self::$summaryRepository->read($workarea);
        $recalledAssessment = $recalledSummary->assess();
        $this->assertEquals($assessment,$recalledAssessment);
    }


    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testSummaryDeletePlayer()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport ProAm']);
        $modelId = $model->getId();
        /**
         * @var \App\Entity\Sales\Iface\Player $fullGoldPlayer
         * @var \App\Entity\Sales\Iface\Player $preBronzePlayer
         */
        list($fullGoldPlayer,$preBronzePlayer,$workarea) =  $this->generateProAmCoupleTrio(
            'Standard',
            'Professional',
            'Full Gold',
            'Pre Bronze',
            30,40);
        $goldEvents=$fullGoldPlayer->getEvents();
        $goldKeys = array_keys($goldEvents[$modelId]);
        $fullGoldPlayer->setSelections([$modelId=>$goldKeys]);
        $bronzeEvents = $preBronzePlayer->getEvents();
        $bronzeKeys = array_keys($bronzeEvents[$modelId]);
        $preBronzePlayer->setSelections([$modelId=>$bronzeKeys]);
        $summary=self::$summaryRepository->read($workarea);
        $summary->add($fullGoldPlayer);
        $summary->add($preBronzePlayer);
        $expected = ['comp'=>[4=>['dances'=>18,'charge'=>216],5=>['dances'=>12,'charge'=>144]],'exam'=>[]];
        $this->assertEquals($expected, $summary->assess());
        $fullGoldPlayerId = $fullGoldPlayer->getId();
        $summary->removePlayer($fullGoldPlayerId);
        unset($expected['comp'][$fullGoldPlayerId]);
        $this->assertEquals($expected,$summary->assess());
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testSummaryDeleteParticipant()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport ProAm']);
        $modelId = $model->getId();
        /**
         * @var \App\Entity\Sales\Iface\Player $fullGoldPlayer
         * @var \App\Entity\Sales\Iface\Player $preBronzePlayer
         */
        list($fullGoldPlayer,$preBronzePlayer,$workarea) =  $this->generateProAmCoupleTrio(
                                                                'Standard',
                                                                'Professional',
                                                                'Full Gold',
                                                                'Pre Bronze',
                                                                30,40);
        $goldEvents=$fullGoldPlayer->getEvents();
        $goldKeys = array_keys($goldEvents[$modelId]);
        $fullGoldPlayer->setSelections([$modelId=>$goldKeys]);
        $bronzeEvents = $preBronzePlayer->getEvents();
        $bronzeKeys = array_keys($bronzeEvents[$modelId]);
        $preBronzePlayer->setSelections([$modelId=>$bronzeKeys]);
        $summary=self::$summaryRepository->read($workarea);
        $summary->add($preBronzePlayer)
                ->add($fullGoldPlayer);
        list($p0,$p1)=$preBronzePlayer->getParticipantIds();
        $summary->removeParticipant($p1);
        $assessment=$summary->assess();
        $this->assertEquals(['comp'=>[4=>['dances'=>18,'charge'=>216]],'exam'=>[]],$assessment);

    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testSummaryPreJSON()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport ProAm']);
        $modelId = $model->getId();
        /**
         * @var \App\Entity\Sales\Iface\Player $fullGoldPlayer
         * @var \App\Entity\Sales\Iface\Player $preBronzePlayer
         */
        list($fullGoldPlayer,$preBronzePlayer,$workarea) =  $this->generateProAmCoupleTrio(
                                                                'Standard',
                                                                'Professional',
                                                                'Full Gold',
                                                                'Pre Bronze',
                                                                30,40);
        $goldEvents=$fullGoldPlayer->getEvents();
        $goldKeys = array_keys($goldEvents[$modelId]);
        $fullGoldPlayer->setSelections([$modelId=>$goldKeys]);
        $bronzeEvents = $preBronzePlayer->getEvents();
        $bronzeKeys = array_keys($bronzeEvents[$modelId]);
        $preBronzePlayer->setSelections([$modelId=>$bronzeKeys]);
        $summary=self::$summaryRepository->read($workarea);
        $summary->add($preBronzePlayer)
                ->add($fullGoldPlayer);
        $preJSON = $summary->preJSON();
        $this->assertEquals(['participation','eventDescription'],array_keys($preJSON));
        $playerIds=array_keys($preJSON['participation']);
        foreach($playerIds as $playerId){
            $value=$preJSON['participation'][$playerId];
            $this->assertEquals(['participants','idModelEvents'],array_keys($value));
            foreach($preJSON['participation'][$playerId]['participants'] as $participant){
               $this->assertEquals(['first','last','sex','years','typeA','typeB'],array_keys($participant));
            }
        }

        foreach($preJSON['eventDescription'] as $modelId=>$eventList) {
            foreach($eventList as $eventId=>$description) {
                $this->assertEquals(['age','tag','type','style','dances','proficiency','id','model','etag'],
                                     array_keys($description));
            }
        }
    }
}