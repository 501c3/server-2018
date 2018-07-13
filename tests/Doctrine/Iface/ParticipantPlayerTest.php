<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/23/18
 * Time: 9:05 AM
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
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use App\Exceptions\ClassifyException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Sales\ContactRepository;
use App\Repository\Sales\Iface\ParticipantRepository;
use App\Repository\Sales\Iface\PlayerRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\WorkareaRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class ParticipantPlayerTest extends KernelTestCase
{
    const ISTD_PROFICIENCIES =
        [
            'Pre Bronze',
            'Bronze',
            'Silver',
            'Gold'
        ];
    const AMATEUR_PROFICIENCIES =
        [
            'Newcomer',
            'Bronze',
            'Silver',
            'Gold',
            'Novice',
            'Pre Championship',
            'Championship'
        ];


    const AMATEUR_TO_ISTD_PROFICIENCIES =
        [
            'Newcomer'=> 'Pre Bronze',
            'Bronze' => 'Bronze',
            'Silver' => 'Silver',
            'Gold' => 'Gold'
        ];

    const PROAM_TO_ISTD_PROFICIENCIES =
        [
            'Newcomer'=>'Pre Bronze',
            'Pre Bronze'=>'Pre Bronze',
            'Intermediate Bronze'=>'Bronze',
            'Full Bronze'=>'Bronze',
            'Open Bronze'=>'Bronze',
            'Pre Silver'=>'Silver',
            'Intermediate Silver'=>'Silver',
            'Full Silver'=>'Silver',
            'Open Silver'=>'Silver',
            'Pre Gold'=>'Gold',
            'Intermediate Gold',
            'Full Gold'=>'Gold',
            'Open Gold'=>'Gold'
        ];


    const PROAM_PROFICIENCIES =
        [
            'Newcomer',
            'Pre Bronze',
            'Intermediate Bronze',
            'Full Bronze',
            'Open Bronze',

            'Pre Silver',
            'Intermediate Silver',
            'Full Silver',
            'Open Silver',

            'Pre Gold',
            'Intermediate Gold',
            'Full Gold',
            'Open Gold',

            'Gold Star 1',
            'Gold Star 2'
        ];
    const ISTD_AGES =
        [
            'Under 6',
            'Under 8',
            'Under 12',
            'Junior 12-16',
            'Adult 16-50',
            'Senior 50'
        ];
    const AMATEUR_AGES=
        [
            'Baby',
            'Juvenile',
            'Preteen 1',
            'Preteen 2',
            'Junior 1',
            'Junior 2',
            'Youth',
            'Adult',
            'Senior 1',
            'Senior 2',
            'Senior 3',
            'Senior 4',
            'Senior 5'
        ];

    const ISTD_AGES_EXPECTED=
        [
            5=>'Under 6',
            7=>'Under 8',
            11=>'Under 12',
            13=>'Junior 12-16',
            16=>'Adult 16-50',
            49=>"Adult 16-50",
            51=>"Senior 50"
    ];

    const AMATEUR_AGES_EXPECTED=

            [
                75=>[
                        70=>'Senior 5',
                        60=>'Senior 4',
                        50=>'Senior 3',
                        40=>'Senior 2',
                        30=>'Senior 1',
                        19=>'Adult',
                        17=>'Adult',
                    ],
                65=>[
                        60=>'Senior 4',
                        50=>'Senior 3',
                        40=>'Senior 2',
                        30=>'Senior 1',
                        19=>'Adult',
                        17=>'Adult'
                    ],
                55=>[
                        50=>'Senior 3',
                        40=>'Senior 2',
                        30=>'Senior 1',
                        19=>'Adult',
                        18=>'Adult'
                    ],
                45=>[
                        40=>'Senior 2',
                        30=>'Senior 1',
                        19=>'Adult',
                        17=>'Adult'
                    ],
                35=> [
                        30=>'Senior 1',
                        19=>'Adult',
                        17=>'Adult'
                    ],
                19=> [17=>'Adult'],
                16=> [16=>'Youth',
                      14=>'Youth',
                      12=>'Youth'],
                14=> [14=>'Junior 2',
                      12=>'Junior 2',
                      10=>'Junior 2',],
                12=> [12=>'Junior 1',
                      10=>'Junior 1',
                       7=>'Junior 1'],
                10=> [10=>'Preteen 2',
                      7=>'Preteen 2'],
                7=> [7=>'Preteen 1',
                     5=>'Preteen 1'],
                5=> [5=>'Juvenile',
                     4=>'Juvenile',
                     3=>'Juvenile'],
                2=> [2=>'Baby']];

    const LOWER_AGE_TO_SEE =
        [
            'Senior 5'=>'Senior 4',
            'Senior 4'=>'Senior 3',
            'Senior 3'=>'Senior 2',
            'Senior 2'=>'Senior 1',
            'Senior 1'=>'Adult'
        ];

    const HIGHER_AGE_TO_SEE =
        [
            'Baby'=>'Juvenile',
            'Juvenile'=>'Preteen 1',
            'Preteen 1'=>'Preteen 2',
            'Preteen 2'=>'Junior 1',
            'Junior 1'=>'Junior 2',
            'Junior 2'=>'Youth',
            'Youth'=>'Adult',
        ];

    const HIGHER_PROFICIENCY_TO_SEE_AMATEUR =
        [
            'Newcomer'=>'Bronze',
            'Bronze'=>'Silver',
            'Silver'=>'Gold',
            'Gold'=>'Novice',
            'Novice'=>'Pre Championship',
            'Pre Championship'=> 'Championship'
        ];



    const MIXED_PROFICIENCY_AMATEUR =
        [
            'Newcomer'=>['Bronze'=>'Bronze',
                         'Silver'=>'Silver'],
            'Bronze'=>['Silver'=>'Silver',
                       'Gold'=>'Gold'],
            'Silver'=>['Gold'=>'Gold',
                       'Novice'=>'Novice'],
            'Gold'=>['Novice'=>'Novice',
                     'Pre Championship'=>'Pre Championship'],
            'Novice'=>['Pre Championship'=>'Pre Championship',
                       'Championship'=>'Championship'] ,
            'Pre Championship'=> ['Championship'=>'Championship']
        ];


    const AMATEUR_AGES_NOMINAL=

        [
            75=> 'Senior 5',
            65=>'Senior 4',
            55=>'Senior 3',
            45=>'Senior 2',
            35=>'Senior 1',
            19=> 'Adult',
            16=> 'Youth',
            14=> 'Junior 2',
            12=> 'Junior 1',
            10=> 'Preteen 2',
            7=> 'Preteen 1',
            5=> 'Juvenile',
            2=> 'Baby'
        ];

    const ISTD_AGES_NOMINAL=
        [
            50=>'Senior 50',
            16=>'Adult 16-50',
            12=>'Junior 12-16',
             8=>'Under 12',
             6=>'Under 8',
             3=>'Under 6'
        ];

    const PLAYER_EVENT_PROAM =
        ['Pre Bronze'=>['Pre Bronze','Intermediate Bronze','Full Bronze'],
            'Intermediate Bronze'=>['Intermediate Bronze','Full Bronze','Open Bronze'],
            'Full Bronze'=>['Full Bronze','Open Bronze','Pre Silver'],
            'Open Bronze'=>['Open Bronze','Pre Silver','Intermediate Silver'],
            'Pre Silver'=>['Pre Silver','Intermediate Silver','Full Silver'],
            'Intermediate Silver'=>['Intermediate Silver','Full Silver','Open Silver'],
            'Full Silver'=>['Full Silver','Open Silver','Pre Gold'],
            'Open Silver'=>['Open Silver','Pre Gold','Intermediate Gold'],
            'Pre Gold'=>['Pre Gold','Intermediate Gold','Full Gold'],
            'Intermediate Gold'=>['Intermediate Gold','Full Gold','Open Gold'],
            'Full Gold'=>['Full Gold','Open Gold'],
            'Open Gold'=>['Open Gold']];

    const PLAYER_EVENT_AMATEUR =
        ['Social'=>['Social'],
            'Newcomer'=>['Newcomer','Bronze','Silver-Gold','Bronze-Silver','Social'],
            'Bronze'=>['Bronze','Silver','Silver-Gold','Bronze-Silver','Silver-Gold','Social'],
            'Silver'=>['Silver','Gold','Silver-Gold','Bronze-Silver','Silver-Gold','Social'],
            'Gold'=>['Gold','Novice','Silver-Gold','Social'],
            'Novice'=>['Novice','Pre Championship','Social'],
            'Pre Championship'=>['Pre Championship','Championship','Social'],
            'Championship'=>['Championship','Social']];
    const PLAYER_EVENT_ISTD =
        ['Pre Bronze'=>['Pre Bronze'],
            'Bronze'=>['Bronze'],
            'Silver'=>['Silver'],
            'Gold'=>['Gold']];

    const PLAYER_EVENT_AGES_USA =
        ['Baby'=>['Baby','Juvenile','Youngster'],
            'Juvenile'=>['Juvenile','Preteen 1','Youngster'],
            'Preteen 1'=>['Preteen 1','Preteen 2','Youngster'],
            'Preteen 2'=>['Preteen 2','Junior 1','Youngster'],
            'Junior 1'=>['Junior 1','Junior 2'],
            'Junior 2'=>['Junior 2','Youth'],
            'Youth'=>['Youth','Adult'],
            'Adult'=>['Adult'],
            'Senior 1'=>['Senior 1','Adult'],
            'Senior 2'=>['Senior 2','Senior 1'],
            'Senior 3'=>['Senior 3','Senior 2'],
            'Senior 4'=>['Senior 4','Senior 3'],
            'Senior 5'=>['Senior 5','Senior 4'],
            'Adult Youngster'=>['Adult Youngster'],
            'Senior Youngster'=>['Senior Youngster'],
            ''];

    const PLAYER_EVENT_AGES_ISTD =
        ['Under 6'=>['Under 6'],
            'Under 8'=>['Under 8'],
            'Under 12'=>['Under 12'],
            'Junior 12-16'=>['Junior 12-16'],
            'Adult 16-50'=>['Adult 16-50'],
            'Senior 50'=>['Senior 50']];



    const PROAM_PROFICIENCIES_NOMINAL =
        [
            'Pre Bronze',
            'Intermediate Bronze',
            'Full Bronze',
            'Open Bronze',

            'Pre Silver',
            'Intermediate Silver',
            'Full Silver',
            'Open Silver',

            'Pre Gold',
            'Intermediate Gold',
            'Full Gold',
            'Open Gold',

            'Gold Star 1',
            'Gold Star 2'
        ];


    const PROAM_TO_AMATEUR_PROFICIENCY =
        [
            'Pre Bronze'=>'Bronze',
            'Intermediate Bronze'=>'Bronze',
            'Full Bronze'=>'Bronze',
            'Open Bronze'=>'Bronze',

            'Pre Silver'=>'Silver',
            'Intermediate Silver'=>'Silver',
            'Full Silver'=>'Silver',
            'Open Silver'=>'Silver',

            'Pre Gold'=>'Gold',
            'Intermediate Gold'=>'Gold',
            'Full Gold'=>'Gold',
            'Open Gold'=>'Novice',

            'Gold Star 1'=>'Pre Championship',
            'Gold Star 2'=>'Championship'
        ];


    const AMATEUR_TO_ISTD_AGES =
        [
            'Senior 5'=>'Senior 50',
            'Senior 4'=>'Senior 50',
            'Senior 3'=>'Senior 50',
            'Senior 2'=>'Adult 16-50',
            'Senior 1'=>'Adult 16-50',
            'Adult'=>'Adult 16-50',
            'Youth'=>'Adult 16-50',
            'Junior 2'=>'Junior 12-16',
            'Junior 1'=>'Junior 12-16',
            'Preteen 2'=>'Under 12',
            'Preteen 1'=>'Under 8',
            'Juvenile'=>'Under 6',
            'Baby'=>'Under 6'
        ];

    const AMATEUR_SOLO_PROFICIENCIES =
        [
            'Newcomer',
            'Bronze',
            'Silver',
            'Gold'
        ];

    const AMATEUR_SOLO_AGES =
        [
            10=> 'Preteen 2',
            7=> 'Preteen 1',
            5=> 'Juvenile',
            2=> 'Baby'
        ];

    /** @var ParticipantPoolGenerator */
    private static $participantPoolGenerator;

    /** @var PlayerPoolGenerator */
    private  static $playerPoolGenerator;

    /** @var PlayerRepository */
    private static $ifacePlayerRepository;

    /** @var WorkareaRepository */
    private static $workareaRepository;

    /** @var ContactRepository */
    private static $contactRepository;

    /** @var TagRepository */
    private static $tagRepository;

    /** @var ParticipantRepository */
    private static $participantRepository;

    /** @var ModelRepository */
    private static $modelRepository;

    private static $count;

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
     * @throws ORMException
     * @throws OptimisticLockException
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
        self::$participantRepository = new ParticipantRepository($valueRepository,
                                                                $modelRepository,
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
        $playerPoolGenerator = new PlayerPoolGenerator(
                                    $competitionRepository,
                                    $modelRepository,
                                    $valueRepository,
                                    self::$participantPoolGenerator);
        self::$playerPoolGenerator=$playerPoolGenerator;
        self::$modelRepository=$modelRepository;
    }


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
        self::$count++;
        $contact->setFirst(substr($first,0,40))
            ->setLast(substr($last,0,40))
            ->setCity('Sandy Springs')
            ->setSt('GA')
            ->setCountry('United States')
            ->setPhone('(678)235-8395')
            ->setOrganization("Organization".self::$count)
            ->setEmail('mgarber+'.self::$count.'@georgiasport.org');
        $contact->getWorkarea()->add($workarea);
        $em=self::$contactRepository->getEntityManager();
        $em->persist($contact);
        $em->flush();
        return $contact;
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function testISTDCoupleAcrossProficiencyAge()
    {
        /** @var Model $model */
        $model=self::$modelRepository->findOneBy(['name'=>'ISTD Medal Exams']);
        $modelId = $model->getId();
        $contact = self::generateContact('ISTD','Couple');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        foreach(self::ISTD_PROFICIENCIES as $proficiency) {
            foreach(self::ISTD_AGES_EXPECTED as $years=>$age) {
                foreach(['Latin','Standard','Rhythm','Smooth'] as $genre){
                    $leader = $gen->getStudent( 'medal', 'M', $genre, $proficiency, $years );
                    $follower = $gen->getStudent( 'medal', 'F', $genre, $proficiency, $years );

                    self::$participantRepository->save( $workarea, $leader );
                    self::$participantRepository->save( $workarea, $follower );
                    $couplePlayer = self::$ifacePlayerRepository->create( $workarea, $leader->getId(), $follower->getId() );
                    $description = $couplePlayer->describe();
                    $actualQualifications = $description['qualifications']['ISTD Medal Exams'][$genre];
                    $expectedQualifications = ['genre' => $genre,
                                                'proficiency' => $proficiency,
                                                'age' => $age,
                                                'type' => 'Couple'];
                    $this->assertArraySubset( $expectedQualifications, $actualQualifications );
                    switch ($genre) {
                        case 'Rhythm':
                        case 'Smooth':
                            $expectedEvents = ['style' => 'American',
                                                'proficiency' => $proficiency,
                                                'age' => $age,
                                                'type' => 'Couple'];
                            foreach ($description['events'][$modelId] as $actualEvent) {
                                $this->assertArraySubset( $expectedEvents, $actualEvent );
                            }
                            break;
                        case 'Latin':
                        case 'Standard':
                            $expectedEvents = ['style' => 'International',
                                                'proficiency' => $proficiency,
                                                'age' => $age,
                                                'type' => 'Couple'];
                            foreach ($description['events'][$modelId] as $actualEvent) {
                                $this->assertArraySubset( $expectedEvents, $actualEvent );
                            }
                    }
                }
            }
        }
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function testISTDSoloAcrossProficiencyAge()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'ISTD Medal Exams']);
        $modelId = $model->getId();
        $contact = self::generateContact('ISTD','Couple');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        foreach(self::ISTD_PROFICIENCIES as $proficiency) {
            foreach (self::ISTD_AGES_EXPECTED as $years => $age) {
                foreach (['Latin', 'Standard', 'Rhythm', 'Smooth'] as $genre) {
                    $teacher = $gen->getAmateurTeacher( 'medal-amateur', 'M', $genre, 'Championship', 30 );
                    $student = $gen->getStudent( 'medal', 'F', $genre, $proficiency, $years );
                    self::$participantRepository->save( $workarea, $teacher );
                    self::$participantRepository->save( $workarea, $student );
                    $couplePlayer = self::$ifacePlayerRepository->create( $workarea, $student->getId(),$teacher->getId() );
                    $description = $couplePlayer->describe();
                    $expectedQualifications = ['genre' => $genre,
                                                'proficiency' => $proficiency,
                                                'age' => $age,
                                                'type' => 'Solo'];
                    $actualQualifications = $description['qualifications']['ISTD Medal Exams'][$genre];
                    $this->assertArraySubset( $expectedQualifications, $actualQualifications );
                    switch ($genre) {
                        case 'Rhythm':
                        case 'Smooth':
                            $expectedEvents = ['style' => 'American',
                                                'proficiency' => $proficiency,
                                                'age' => $age,
                                                'type' => 'Solo'];
                            foreach ($description['events'][$modelId] as $actualEvent) {
                                $this->assertArraySubset( $expectedEvents, $actualEvent );
                            }
                            break;
                        case 'Latin':
                        case 'Standard':
                            $expectedEvents = ['style' => 'International',
                                                'proficiency' => $proficiency,
                                                'age' => $age,
                                                'type' => 'Solo'];
                            foreach ($description['events'][$modelId] as $actualEvent) {
                                $this->assertArraySubset( $expectedEvents, $actualEvent );
                            }
                    }
                }
            }
        }
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function testGADSAmateurEqualProficienciesAcrossAges()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport Amateur']);
        $modelId = $model->getId();
        $contact = self::generateContact('GADS','Amateur');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        foreach(self::AMATEUR_PROFICIENCIES as $proficiency) {
            foreach (self::AMATEUR_AGES_EXPECTED as $olderYears => $ageList) {
                foreach($ageList as $youngerYears => $expectedAgeName){
                    foreach (['Latin', 'Standard', 'Rhythm', 'Smooth'] as $genre) {
                        if(($olderYears>=19 && $youngerYears<14)) continue;
                        $gent = $gen->getStudent( 'amateur', 'M', $genre, $proficiency, $olderYears );
                        $lady = $gen->getStudent( 'amateur', 'F', $genre, $proficiency, $youngerYears );
                        self::$participantRepository->save( $workarea, $gent );
                        self::$participantRepository->save( $workarea, $lady );
                        $couplePlayer = self::$ifacePlayerRepository->create( $workarea, $gent->getId(),$lady->getId());
                        $description = $couplePlayer->describe();
                        $expectedQualifications = ['genre' => $genre,
                                                    'proficiency' => $proficiency,
                                                    'age' => $expectedAgeName,
                                                    'type' => 'Amateur'];
                        $actualQualifications = $description['qualifications']['Georgia DanceSport Amateur'][$genre];
                        $this->assertArraySubset( $expectedQualifications, $actualQualifications );
                        $style = $genre=='Rhythm' || $genre=='Smooth'?'American':'International';
                        if ($proficiency == 'Championship') continue;
                        $higherProficiency = self::HIGHER_PROFICIENCY_TO_SEE_AMATEUR[$proficiency];
                        $currentAgeSeen = 0;
                        $currentProficiencySeen = 0;
                        $higherProficiencySeen = 0;
                        foreach ($description['events'][$modelId] as $actualEvent) {
                            $this->assertTrue($actualEvent['style']==$style);

                            if ($actualEvent['age'] == $expectedAgeName) {
                                $currentAgeSeen++;
                            }
                            if ($actualEvent['proficiency'] == $proficiency) {
                                $currentProficiencySeen++;
                            }
                            if ($actualEvent['proficiency'] == $higherProficiency) {
                                $higherProficiencySeen++;
                            }
                        }
                        $this->assertGreaterThanOrEqual( 1, $higherProficiencySeen,
                            "Did not see higher proficiency event for $proficiency, $expectedAgeName qualification" );
                        $this->assertGreaterThanOrEqual( 1, $currentProficiencySeen,
                            "Did not see event for $proficiency, $expectedAgeName qualification" );
                        $this->assertGreaterThanOrEqual( 1, $currentAgeSeen,
                            "Did not see event for $proficiency, $expectedAgeName qualification" );

                        if ($youngerYears >= 17 && $olderYears >= 19 && $expectedAgeName != 'Adult') {
                            $lowerAgeSeen = 0;
                            $lowerAge = self::LOWER_AGE_TO_SEE[$expectedAgeName];
                            foreach ($description['events'][$modelId] as $actualEvent) {
                                if ($actualEvent['age'] == $lowerAge) {
                                    $lowerAgeSeen++;
                                }
                            }
                            $this->assertGreaterThanOrEqual( 1, $lowerAgeSeen,
                                "Did not see a lower age event for $proficiency, $expectedAgeName qualification" );

                        } elseif ($youngerYears < 19 && $olderYears < 19) {
                            $higherAgeSeen = 0;
                            $higherAge = self::HIGHER_AGE_TO_SEE[$expectedAgeName];
                            foreach ($description['events'][$modelId] as $actualEvent) {
                                if ($actualEvent['age'] == $higherAge) {
                                    $higherAgeSeen++;
                                }
                            }
                            $this->assertGreaterThanOrEqual( 1, $higherAgeSeen,
                                "Did not see a higher age event for $proficiency, $expectedAgeName qualification" );
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function testGADSAmateurEqualAgesAcrossProficiencies()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport Amateur']);
        $modelId = $model->getId();
        $contact = self::generateContact('GADS','Amateur');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        foreach(self::MIXED_PROFICIENCY_AMATEUR as $lowProficiency=>$higherProficiencies){
            foreach($higherProficiencies as $highProficiency=>$expectedProficiency) {
                foreach(self::AMATEUR_AGES_NOMINAL as $years=>$age) {
                    foreach (['Latin', 'Standard', 'Rhythm', 'Smooth'] as $genre) {
                        $gent = $gen->getStudent( 'amateur', 'M', $genre, $lowProficiency, $years );
                        $lady = $gen->getStudent( 'amateur', 'F', $genre, $highProficiency, $years );
                        self::$participantRepository->save( $workarea, $gent );
                        self::$participantRepository->save( $workarea, $lady );
                        $couplePlayer = self::$ifacePlayerRepository->create( $workarea, $gent->getId(),$lady->getId());
                        $description = $couplePlayer->describe();
                        $expectedQualifications = ['genre' => $genre,
                                                    'proficiency' => $expectedProficiency,
                                                    'age' => $age,
                                                    'type' => 'Amateur'];
                        $actualQualifications = $description['qualifications']['Georgia DanceSport Amateur'][$genre];
                        $this->assertArraySubset($expectedQualifications,$actualQualifications);
                        $style = $genre=='Rhythm' || $genre=='Smooth'?'American':'International';
                        if ($highProficiency == 'Championship') continue;
                        $higherProficiency = self::HIGHER_PROFICIENCY_TO_SEE_AMATEUR[$highProficiency];
                        $currentAgeSeen = 0;
                        $currentProficiencySeen = 0;
                        $higherProficiencySeen = 0;
                        foreach ($description['events'][$modelId] as $actualEvent) {
                            $this->assertTrue($actualEvent['style']==$style);

                            if ($actualEvent['age'] == $age) {
                                $currentAgeSeen++;
                            }
                            if ($actualEvent['proficiency'] == $highProficiency) {
                                $currentProficiencySeen++;
                            }
                            if ($actualEvent['proficiency'] == $higherProficiency) {
                                $higherProficiencySeen++;
                            }
                        }
                        $this->assertGreaterThanOrEqual( 1, $higherProficiencySeen,
                            "Did not see higher proficiency event for $highProficiency, $age qualification" );
                        $this->assertGreaterThanOrEqual( 1, $currentProficiencySeen,
                            "Did not see event for $highProficiency, $age qualification" );
                        $this->assertGreaterThanOrEqual( 1, $currentAgeSeen,
                            "Did not see event for $highProficiency, $age qualification" );

                        if ($years>=19 && $age!='Adult') {
                            $lowerAgeSeen = 0;
                            $lowerAge = self::LOWER_AGE_TO_SEE[$age];
                            foreach ($description['events'][$modelId] as $actualEvent) {
                                if ($actualEvent['age'] == $lowerAge) {
                                    $lowerAgeSeen++;
                                }
                            }
                            $this->assertGreaterThanOrEqual( 1, $lowerAgeSeen,
                                "Did not see a lower age event for $highProficiency, $age qualification" );

                        } elseif($age!='Adult'){
                            $higherAgeSeen = 0;
                            $higherAge = self::HIGHER_AGE_TO_SEE[$age];
                            foreach ($description['events'][$modelId] as $actualEvent) {
                                if ($actualEvent['age'] == $higherAge) {
                                    $higherAgeSeen++;
                                }
                            }
                            $this->assertGreaterThanOrEqual( 1, $higherAgeSeen,
                                "Did not see a higher age event for $highProficiency, $age qualification" );
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function testGADSAmateurUsingProAmProficiencies()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport Amateur']);
        $modelId = $model->getId();
        $contact = self::generateContact('GADS','Amateur');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        foreach(self::PROAM_PROFICIENCIES_NOMINAL as $proficiency){
            foreach(self::AMATEUR_AGES_NOMINAL as $years=>$age){
                foreach (['Latin', 'Standard', 'Rhythm', 'Smooth'] as $genre) {
                    $gent = $gen->getStudent( 'amateur-proam', 'M', $genre, $proficiency, $years );
                    $lady = $gen->getStudent( 'amateur-proam', 'F', $genre, $proficiency, $years );
                    self::$participantRepository->save( $workarea, $gent );
                    self::$participantRepository->save( $workarea, $lady );
                    $couplePlayer = self::$ifacePlayerRepository->create( $workarea, $gent->getId(),$lady->getId());
                    $description = $couplePlayer->describe();
                    $expectedQualifications = ['genre' => $genre,
                                                'proficiency' => self::PROAM_TO_AMATEUR_PROFICIENCY[$proficiency],
                                                'age' => $age,
                                                'type' => 'Amateur'];
                    $actualQualifications = $description['qualifications']['Georgia DanceSport Amateur'][$genre];
                    $this->assertArraySubset($expectedQualifications,$actualQualifications);
                    $style = $genre=='Rhythm' || $genre=='Smooth'?'American':'International';
                    if ($proficiency == 'Gold Star 1') continue;
                    if ($proficiency == 'Gold Star 2') continue;
                    $amateurProficiency = self::PROAM_TO_AMATEUR_PROFICIENCY[$proficiency];
                    $higherProficiency = self::HIGHER_PROFICIENCY_TO_SEE_AMATEUR[$amateurProficiency];
                    $currentAgeSeen = 0;
                    $currentProficiencySeen = 0;
                    $higherProficiencySeen = 0;
                    foreach ($description['events'][$modelId] as $actualEvent) {
                        $this->assertTrue($actualEvent['style']==$style);

                        if ($actualEvent['age'] == $age) {
                            $currentAgeSeen++;
                        }
                        if ($actualEvent['proficiency'] == $amateurProficiency) {
                            $currentProficiencySeen++;
                        }
                        if ($actualEvent['proficiency'] == $higherProficiency) {
                            $higherProficiencySeen++;
                        }
                    }
                    $this->assertGreaterThanOrEqual( 1, $higherProficiencySeen,
                        "Did not see higher proficiency event for $proficiency, $age qualification" );
                    $this->assertGreaterThanOrEqual( 1, $currentProficiencySeen,
                        "Did not see event for $amateurProficiency, $age qualification" );
                    $this->assertGreaterThanOrEqual( 1, $currentAgeSeen,
                        "Did not see event for $amateurProficiency, $age qualification" );

                    if ($years>=19 && $age!='Adult') {
                        $lowerAgeSeen = 0;
                        $lowerAge = self::LOWER_AGE_TO_SEE[$age];
                        foreach ($description['events'][$modelId] as $actualEvent) {
                            if ($actualEvent['age'] == $lowerAge) {
                                $lowerAgeSeen++;
                            }
                        }
                        $this->assertGreaterThanOrEqual( 1, $lowerAgeSeen,
                            "Did not see a lower age event for $amateurProficiency, $age qualification" );

                    } elseif($age!='Adult'){
                        $higherAgeSeen = 0;
                        $higherAge = self::HIGHER_AGE_TO_SEE[$age];
                        foreach ($description['events'][$modelId] as $actualEvent) {
                            if ($actualEvent['age'] == $higherAge) {
                                $higherAgeSeen++;
                            }
                        }
                        $this->assertGreaterThanOrEqual( 1, $higherAgeSeen,
                            "Did not see a higher age event for $amateurProficiency, $age qualification" );
                    }
                }
            }
        }
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function testISTDUsingGADSAmateurProficienciesAndAges()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'ISTD Medal Exams']);
        $modelId = $model->getId();
        $contact = self::generateContact('GADS','Amateur');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        foreach(self::AMATEUR_TO_ISTD_PROFICIENCIES as $amateurProficiency=>$istdProficiency){
            foreach(self::AMATEUR_AGES_NOMINAL as $years=>$age){
                foreach (['Latin', 'Standard', 'Rhythm', 'Smooth'] as $genre) {
                    $gent = $gen->getStudent( 'medal-amateur', 'M', $genre, $amateurProficiency, $years );
                    $lady = $gen->getStudent( 'medal-amateur', 'F', $genre, $amateurProficiency, $years );
                    self::$participantRepository->save( $workarea, $gent );
                    self::$participantRepository->save( $workarea, $lady );
                    $couplePlayer = self::$ifacePlayerRepository->create( $workarea, $gent->getId(),$lady->getId());
                    $description = $couplePlayer->describe();
                    $style = $genre=='Rhythm' || $genre=='Smooth'?'American':'International';
                    foreach($description['events'][$modelId] as $event) {
                        $this->assertEquals($style,$event['style']);
                        $this->assertEquals($istdProficiency,$event['proficiency']);
                        $this->assertEquals('Couple',$event['type']);
                    }
                }
            }
        }
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function testISTDUsingGADSProAmProficienciesAndAges()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'ISTD Medal Exams']);
        $modelId = $model->getId();
        $contact = self::generateContact('GADS','Amateur');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        foreach(self::PROAM_TO_ISTD_PROFICIENCIES as $proAmProficiency=>$istdProficiency) {
            foreach (self::AMATEUR_AGES_NOMINAL as $years => $age) {
                foreach (['Latin', 'Standard', 'Rhythm', 'Smooth'] as $genre) {
                    $gent = $gen->getStudent( 'medal-amateur-proam', 'M', $genre, $proAmProficiency, $years );
                    $lady = $gen->getStudent( 'medal-amateur-proam', 'F', $genre, $proAmProficiency, $years );
                    self::$participantRepository->save( $workarea, $gent );
                    self::$participantRepository->save( $workarea, $lady );
                    $couplePlayer = self::$ifacePlayerRepository->create( $workarea, $gent->getId(),$lady->getId());
                    $description = $couplePlayer->describe();
                    $style = $genre=='Rhythm' || $genre=='Smooth'?'American':'International';
                    foreach($description['events'][$modelId] as $event) {
                        $this->assertEquals($style,$event['style']);
                        $this->assertEquals($istdProficiency,$event['proficiency']);
                        $this->assertEquals('Couple',$event['type']);
                    }
                }
            }
        }

    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function testISTDTeacherStudentProficienciesAndAges()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'ISTD Medal Exams']);
        $modelId = $model->getId();
        $contact = self::generateContact('GADS','Amateur');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        foreach (['Latin', 'Standard', 'Rhythm', 'Smooth'] as $genre) {
            $teacher = $gen->getProfessionalTeacher( 'medal-proam', 'M', $genre, 'Professional', 30 );
            self::$participantRepository->save( $workarea, $teacher);
            foreach(self::ISTD_PROFICIENCIES as $proficiency) {
                foreach (self::ISTD_AGES_NOMINAL as $years => $ageName) {
                    $student = $gen->getStudent( 'medal', 'M', $genre, $proficiency, $years );
                    self::$participantRepository->save($workarea,$student);
                    $couplePlayer = self::$ifacePlayerRepository->create( $workarea, $teacher->getId(),$student->getId());
                    $description = $couplePlayer->describe();
                    $style = $genre=='Rhythm' || $genre=='Smooth'?'American':'International';
                    $expectedQualifications = ['genre' => $genre,
                                                'proficiency' => $proficiency,
                                                'age' => $ageName,
                                                'type' => 'Solo'];
                    $actualQualifications = $description['qualifications']['ISTD Medal Exams'][$genre];
                    $this->assertArraySubset($expectedQualifications,$actualQualifications);
                    foreach($description['events'][$modelId] as $event) {
                        $this->assertEquals($style,$event['style']);
                        $this->assertEquals($proficiency,$event['proficiency']);
                        $this->assertEquals('Solo',$event['type']);
                    }
                }
            }
        }
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function testGADSAmateurSolo()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport Amateur']);
        $modelId = $model->getId();
        $contact = self::generateContact('GADS','Solo');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        foreach (['Latin', 'Standard'] as $genre) {
            foreach(self::AMATEUR_SOLO_PROFICIENCIES as $proficiency){
                foreach(self::AMATEUR_SOLO_AGES as $years=>$age){
                    $participant = $gen->getStudent( 'amateur', 'F', $genre, $proficiency, $years );
                    self::$participantRepository->save($workarea,$participant);
                    $soloPlayer = self::$ifacePlayerRepository->create( $workarea, $participant->getId());
                    $description = $soloPlayer->describe();
                    $expectedQualifications = ['genre'=>$genre,
                                              'proficiency'=>$proficiency,
                                              'age'=>$age,
                                              'type'=>'Amateur'];
                    $this->assertArraySubset($expectedQualifications,
                        $description['qualifications'][$model->getName()][$genre]);
                    foreach($description['events'][$modelId] as $event) {
                       $this->assertArraySubset(['style'=>'International','tag'=>'Solo'],$event);
                    }
                }
            }
        }
    }


    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function testGADSAmateurAdultYoungster()
    {

        $contact = self::generateContact('GADS','Adult Youngster');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        $senior = $gen->getStudent( 'amateur', 'M', 'Fun Events', 'Social', 50);
        $adult = $gen->getStudent( 'amateur', 'M', 'Fun Events', 'Social', 30);
        $child = $gen->getStudent('amateur','F','Fun Events','Social',8);

        self::$participantRepository->save($workarea,$senior);
        self::$participantRepository->save($workarea,$adult);
        self::$participantRepository->save($workarea,$child);

        $seniorChildPlayer=self::$ifacePlayerRepository->create( $workarea, $senior->getId(),$child->getId());
        $description = $seniorChildPlayer->describe();
        $actualQualification = $description['qualifications']['Georgia DanceSport Amateur'];
        $expectedQualification = ['Fun Events'=>['genre'=>'Fun Events',
                                                 'proficiency'=>'Social',
                                                 'age'=>'Senior Youngster',
                                                 'type'=>'Amateur']];
        $this->assertArraySubset($expectedQualification,$actualQualification);

        $adultChildPlayer = self::$ifacePlayerRepository->create( $workarea, $adult->getId(),$child->getId());
        $description = $adultChildPlayer->describe();
        $actualQualification = $description['qualifications']['Georgia DanceSport Amateur'];
        $expectedQualification = ['Fun Events'=>['genre'=>'Fun Events',
                                                'proficiency'=>'Social',
                                                'age'=>'Adult Youngster',
                                                'type'=>'Amateur']];
        $this->assertArraySubset($expectedQualification,$actualQualification);
    }

    /**
     * @throws ClassifyException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function testGADSSoloFunEvents()
    {
        $contact = self::generateContact('GADS','Chicken Dance');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        $youngster = $gen->getStudent( 'amateur', 'F', 'Novelty', 'Social', 8);
        self::$participantRepository->save($workarea,$youngster);
        $soloPlayer = self::$ifacePlayerRepository->create( $workarea, $youngster->getId());
        $description = $soloPlayer->describe();
        $actual = $description['events'][2][0];
        $expected=['age'=>'Youngster',
                  'tag'=>'Solo',
                  'style'=>'Fun Events',
                  'dances'=>['CD']];
        $this->assertArraySubset($expected,$actual);

    }

    public function testParticipantList()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport Amateur']);
        $modelId = $model->getId();
        $contact = self::generateContact('GADS','ParticipantListTest');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        $count=0;
        $expected = [];
        foreach(self::AMATEUR_PROFICIENCIES as $proficiency){
            /** @var Participant $gent */
            $gent = $gen->getStudent( 'amateur', 'M', 'Standard', $proficiency, 30 );
            /** @var Participant $lady */
            $lady = $gen->getStudent( 'amateur', 'F', 'Standard', $proficiency, 30 );
            $count+=2;
            self::$participantRepository->save($workarea,$gent);
            self::$participantRepository->save($workarea,$lady);
            $expected["Standard-$proficiency Amateur-M30"]=$gent->getId();
            $expected["Standard-$proficiency Amateur-F30"]=$lady->getId();
        }

        $participantList=self::$participantRepository->fetchList($workarea);
        $this->assertArraySubset($expected,$participantList->preJSON());
        $this->assertArraySubset($participantList->preJSON(),$expected);
    }


    public function testPlayerCoupleList()
    {
        $model=self::$modelRepository->findOneBy(['name'=>'Georgia DanceSport Amateur']);
        $modelId = $model->getId();
        $contact = self::generateContact('GADS','PlayerListTest');
        $workarea=$contact->getWorkarea()->first();
        $gen = self::$participantPoolGenerator;
        $expected = [];
        foreach(self::AMATEUR_PROFICIENCIES as $amateurProficiency){
            /** @var Participant $gent */
            $gent = $gen->getStudent( 'amateur', 'M', 'Standard', $amateurProficiency, 30 );
            /** @var Participant $lady */
            $lady = $gen->getStudent( 'amateur', 'F', 'Standard', $amateurProficiency, 30 );
            self::$participantRepository->save($workarea,$gent);
            self::$participantRepository->save($workarea,$lady);
            /** @var IfacePlayer $player */
            $player=self::$ifacePlayerRepository->create($workarea, $gent->getId(),$lady->getId());
            var_dump($player->describe());die;
        }
    }

}