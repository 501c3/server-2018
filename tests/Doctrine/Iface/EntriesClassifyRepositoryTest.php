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
use App\Exceptions\GeneralException;
use App\Exceptions\MissingException;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\EventRepository;
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
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;



class EntriesClassifyRepositoryTest extends KernelTestCase
{
    /** @var EntriesAssessGenerator */
    private static $entriesAssessGenerator;

    /** @var Tag */
    private static $participantTag;

    /** @var FormRepository */
    private static $formRepository;

    /** @var ContactRepository */
    private static $contactRepository;

    /** @var TagRepository */
    private static $tagRepository;

    /** @var ParticipantRepository */
    private static $ifaceParticipantRepository;

    /** @var IfacePlayerRepository */
    private static $ifacePlayerRepository;

    /** @var WorkareaRepository */
    private static $workareaRepository;

    private static $domainValueHash;

    private static $modelsByName;

    private static $count;

    /** @var Channel */
    private static $channel;

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
        self::$contactRepository = $entityManagerSales->getRepository(Contact::class);
        /** @var WorkareaRepository $workareaRepository */
        self::$workareaRepository = $entityManagerSales->getRepository(Workarea::class);
        /** @var FormRepository $formRepository */
        self::$formRepository = $entityManagerSales->getRepository(Form::class);
        /** @var TagRepository  */
        self::$tagRepository = $entityManagerSales->getRepository(Tag::class);
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

        $ifaceRepository = $entityManagerCompetition->getRepository(Iface::class);

        /** @var Channel $channel */
        self::$channel=$channelRepository->findOneBy(['name'=>'Georgia DanceSport']);

        self::$domainValueHash = $valueRepository->fetchDomainValueHash();

        self::$modelsByName = $modelRepository->fetchModelsByName();

        /** @var ParticipantRepository $ifaceParticipantRepository */
        self::$ifaceParticipantRepository = new ParticipantRepository($valueRepository,
                                                                $modelRepository,
                                                                self::$formRepository,
                                                                self::$tagRepository);


        /** @var IfacePlayerRepository */
        self::$ifacePlayerRepository  = new IfacePlayerRepository(
                                                $valueRepository,
                                                $modelRepository,
                                                self::$tagRepository,
                                                self::$formRepository,
                                                $competitionRepository,
                                                $ifaceRepository,
                                                $playerRepository,
                                                $eventRepository);

        self::$ifacePlayerRepository->initClassifier(self::$channel,true);

        self::$entriesAssessGenerator
            = new EntriesAssessGenerator($channelRepository,
                                        self::$contactRepository,
                                        self::$workareaRepository,
                                        self::$formRepository,
                                        self::$tagRepository ,
                                        $competitionRepository,
                                        $modelRepository,
                                        $valueRepository,
                                        self::$ifaceParticipantRepository,
                                        self::$ifacePlayerRepository);
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
        string $last
    ):Contact
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


    public function generateParticipant(
            string $model,
            string $genre,
            string $proficiency,
            int $years,
            string $sex,
            string $typeA,
            string $typeB) : Participant
    {

        $genreValue = isset(self::$domainValueHash['style'][$genre])?
                                self::$domainValueHash['style'][$genre]:
                                self::$domainValueHash['substyle'][$genre];
        $proficiencyValue = self::$domainValueHash['proficiency'][$proficiency];

        $typeAValue = self::$domainValueHash['type'][$typeA];
        $typeBValue = self::$domainValueHash['type'][$typeB];
        $model = self::$modelsByName[$model];
        $participant = new Participant();
        $participant->setLast($genre.'-'.$proficiency)
                    ->setFirst($sex.$years)
                    ->setSex($sex)
                    ->setYears($years)
                    ->setTypeA($typeAValue)
                    ->setTypeB($typeBValue)
                    ->addModel($model)
                    ->addGenreProficiency($genreValue,$proficiencyValue);
        return $participant;
    }


    /**
     * @throws GeneralException
     * @throws MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testStandardPreChampionship()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-standard-pre-championship.yml' );
        $generator = self::$entriesAssessGenerator;
        $generator->parse($yamlText);
        $this->assertCount($generator->getContactCount(),$generator->getParticipation());
    }


    /**
     * @throws GeneralException
     * @throws MissingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testStandardAmateurTeacherBaby()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-standard-amateur-teacher-baby.yml' );
        $generator = self::$entriesAssessGenerator;
        $generator->parse($yamlText);
        $this->assertEquals($generator->getContactCount(),self::$contactRepository->getCount());
        $this->assertEquals($generator->getParticipantCount(),
                            self::$formRepository->getCount(self::$participantTag));
    }

    /**
     * @throws GeneralException
     * @throws MissingException 
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testCorrect()
    {
        $yamlText = file_get_contents(
            __DIR__ . '/../../Scripts/Yaml/Sales/entries-correct.yml' );
        $generator = self::$entriesAssessGenerator;
        $generator->parse($yamlText);
        $this->assertEquals($generator->getContactCount(),self::$contactRepository->getCount());
        $this->assertEquals($generator->getParticipantCount(),
            self::$formRepository->getCount(self::$participantTag));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testContactSave()
    {
       /** @var ContactRepository $repository */
        $repository = self::$contactRepository;
        $this->generateContact('Mark','Garber');
        $cnt=$repository->createQueryBuilder('u')
                    ->select('count(u.id)')
                    ->getQuery()
                    ->getSingleScalarResult();
        $this->assertEquals(1, $cnt);
    }


    private function generateSaveCouple(Workarea $workarea,
                                        string $model,
                                        string $genre,
                                        string $leaderProficiency,
                                        int $leaderYears,
                                        string $followerProficiency,
                                        int $followerYears)
    {
        $leader=$this->generateParticipant(
                        $model,
                        $genre,
                        $leaderProficiency,
                        $leaderYears,
                        'M',
                        'Amateur',
                        'Student');
        $follower=$this->generateParticipant(
                        $model,
                        $genre,
                        $followerProficiency,
                        $followerYears,
                        'F',
                        'Amateur',
                        'Student');
        self::$ifaceParticipantRepository->save($workarea,$leader);
        self::$ifaceParticipantRepository->save($workarea, $follower);
        return [$leader,$follower];
    }


    /**
     * @param Workarea $workarea
     * @param string $model
     * @param string $genre
     * @param string $leadProficiency
     * @param string $followProficiency
     * @param int $leadAge
     * @param int $followAge
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function leaderFollower(Workarea $workarea,
                                        string $model,
                                        string $genre,
                                        string $leadProficiency,
                                        string $followProficiency,
                                        int $leadAge,
                                        int $followAge)
    {
        $repository = self::$formRepository;
        /**
         * @var Participant $leader
         * @var Participant $follower
         */
        list($leader,$follower)
            =$this->generateSaveCouple($workarea,
            $model,
            $genre,
            $leadProficiency,
            $leadAge,
            $followProficiency,
            $followAge);
        $cnt=$repository->createQueryBuilder('form')
            ->select('count(form.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $this->assertEquals(2, $cnt);
        return [$leader,$follower];
    }


    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     */
    public function testContactISTDParticipantCoupleBabyPreBronze()
    {
        $contact=$this->generateContact('Mark','Garber');
        $workarea=$contact->getWorkarea()->first();
        /**
         * @var Participant $leader
         * @var Participant $follower
         */
        list($leader,$follower) = $this->leaderFollower(
            $workarea,'ISTD Medal Exams','Latin',
            'Pre Bronze','Pre Bronze',5,4);


        $this->assertArraySubset(
            ['id'=>1,'sex'=>'M','years'=>5,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams'], 'genreProficiency'=>['Latin'=>'Pre Bronze']],
            $leader->describe());
        $this->assertArraySubset(
            ['id'=>2,'sex'=>'F','years'=>4,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams'], 'genreProficiency'=>['Latin'=>'Pre Bronze']],
            $follower->describe());

        /** @var IfacePlayer $createdPlayer */
        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $leader,$follower);
        /** @var IfacePlayer $retrievedPlayer */
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());
    }


    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     */
    public function testContactISTDParticipantCoupleUnder8Bronze()
    {

        $contact=$this->generateContact('Mark','Garber');
        /** @var Workarea $workarea */
        $workarea=$contact->getWorkarea()->first();
        /**
         * @var Participant $leader
         * @var Participant $follower
         */
        list($leader,$follower) = $this->leaderFollower(
            $workarea,'ISTD Medal Exams','Standard',
            'Pre Bronze','Bronze',6,7);

        $this->assertArraySubset(
            ['id'=>1,'sex'=>'M','years'=>6,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams'], 'genreProficiency'=>['Standard'=>'Pre Bronze']],
            $leader->describe());
        $this->assertArraySubset(
            ['id'=>2,'sex'=>'F','years'=>7,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams'], 'genreProficiency'=>['Standard'=>'Bronze']],
            $follower->describe());


        /** @var IfacePlayer $createdPlayer */
        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $leader,$follower);
        /** @var IfacePlayer $retrievedPlayer */
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());
        $description=$retrievedPlayer->describe();
        $qualifications = $description['qualifications'];
        $expectedQualifications
            =
            ['ISTD Medal Exams' =>
                [
                'Standard' =>
                    [
                        'genre' => "Standard",
                        'proficiency' =>"Bronze",
                        'age' =>"Under 8",
                        'type' =>"Couple"
                    ]
                ]
            ];
        $this->assertArraySubset($expectedQualifications,$qualifications);
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testContactISTDParticipantCoupleUnder12Silver()
    {

        $contact=$this->generateContact('Mark','Garber');
        /** @var Workarea $workarea */
        $workarea=$contact->getWorkarea()->first();
        /**
         * @var Participant $leader
         * @var Participant $follower
         */
        list($leader,$follower) = $this->leaderFollower(
            $workarea,'ISTD Medal Exams','Standard',
            'Bronze','Silver',11,10);

        $this->assertArraySubset(
            ['id'=>1,'sex'=>'M','years'=>11,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams'], 'genreProficiency'=>['Standard'=>'Bronze']],
            $leader->describe());
        $this->assertArraySubset(
            ['id'=>2,'sex'=>'F','years'=>10,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams'], 'genreProficiency'=>['Standard'=>'Silver']],
            $follower->describe());


        /** @var IfacePlayer $createdPlayer */
        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $leader,$follower);
        /** @var IfacePlayer $retrievedPlayer */
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());
        $description=$retrievedPlayer->describe();
        $qualifications = $description['qualifications'];
        $expectedQualifications
            =
            ['ISTD Medal Exams' =>
                [
                    'Standard' =>
                        [
                            'genre' => "Standard",
                            'proficiency' =>"Silver",
                            'age' =>"Under 12",
                            'type' =>"Couple"
                        ]
                ]
            ];
        $this->assertArraySubset($expectedQualifications,$qualifications);
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testContactISTDParticipantCoupleAdult16To50Gold()
    {

        $contact=$this->generateContact('Mark','Garber');
        /** @var Workarea $workarea */
        $workarea=$contact->getWorkarea()->first();
        /**
         * @var Participant $leader
         * @var Participant $follower
         */
        list($leader,$follower) = $this->leaderFollower(
            $workarea,'ISTD Medal Exams','Smooth',
            'Gold','Silver',19,49);


        $this->assertArraySubset(
            ['id'=>1,'sex'=>'M','years'=>19,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams'], 'genreProficiency'=>['Smooth'=>'Gold']],
            $leader->describe());
        $this->assertArraySubset(
            ['id'=>2,'sex'=>'F','years'=>49,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams'], 'genreProficiency'=>['Smoth'=>'Silver']],
            $follower->describe());


        /** @var IfacePlayer $createdPlayer */
        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $leader,$follower);
        /** @var IfacePlayer $retrievedPlayer */
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());
        $description=$retrievedPlayer->describe();
        //var_dump($description);die;
        $qualifications = $description['qualifications'];
        $expectedQualifications
            =
            ['ISTD Medal Exams' =>
                [
                    'Smooth' =>
                        [
                            'genre' => "Smooth",
                            'proficiency' =>"Gold",
                            'age' =>"Adult 16-50",
                            'type' =>"Couple"
                        ]
                ]
            ];
        $this->assertArraySubset($expectedQualifications,$qualifications);
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testContactISTDParticipantCoupleAdult16To50Silver()
    {

        $contact=$this->generateContact('Mark','Garber');
        /** @var Workarea $workarea */
        $workarea=$contact->getWorkarea()->first();
        /**
         * @var Participant $leader
         * @var Participant $follower
         */
        list($leader,$follower) = $this->leaderFollower(
            $workarea,'ISTD Medal Exams','Rhythm',
            'Bronze','Silver',35,61);

        $this->assertArraySubset(
            ['id'=>1,'sex'=>'M','years'=>35,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams'], 'genreProficiency'=>['Rhythm'=>'Bronze']],
            $leader->describe());
        $this->assertArraySubset(
            ['id'=>2,'sex'=>'F','years'=>61,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams'], 'genreProficiency'=>['Rhythm'=>'Silver']],
            $follower->describe());


        /** @var IfacePlayer $createdPlayer */
        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $leader,$follower);
        /** @var IfacePlayer $retrievedPlayer */
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());
        $description=$retrievedPlayer->describe();
        //var_dump($description);die;
        $qualifications = $description['qualifications'];
        $expectedQualifications
            =
            ['ISTD Medal Exams' =>
                [
                    'Rhythm' =>
                        [
                            'genre' => "Rhythm",
                            'proficiency' =>"Silver",
                            'age' =>"Adult 16-50",
                            'type' =>"Couple"
                        ]
                ]
            ];
        $this->assertArraySubset($expectedQualifications,$qualifications);
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());
    }


    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGADSAmContactAmateurNewcomerCoupleBaby()
    {
        $contact=$this->generateContact('Mark','Garber');
        /** @var Workarea $workarea */
        $workarea=$contact->getWorkarea()->first();

        /**
         * @var Participant $leader
         * @var Participant $follower
         */
        list($leader,$follower) = $this->leaderFollower(
            $workarea,'Georgia DanceSport Amateur','Latin',
            'Newcomer','Newcomer',3,4);

        $this->assertArraySubset(
            ['id'=>1,'sex'=>'M','years'=>3,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['Georgia DanceSport Amateur'], 'genreProficiency'=>['Latin'=>'Newcomer']],
            $leader->describe());
        $this->assertArraySubset(
            ['id'=>2,'sex'=>'F','years'=>4,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['Georgia DanceSport Amateur'], 'genreProficiency'=>['Latin'=>'Newcomer']],
            $follower->describe());


        /** @var IfacePlayer $createdPlayer */
        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $leader,$follower);
        /** @var IfacePlayer $retrievedPlayer */
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());

        $description=$retrievedPlayer->describe();
        $qualifications = $description['qualifications'];

        $expectedQualifications
            =
            ['Georgia DanceSport Amateur' =>
                [
                    'Latin' =>
                        [
                            'genre' => "Latin",
                            'proficiency' =>"Newcomer",
                            'age' =>"Baby",
                            'type' =>"Amateur"
                        ]
                ]
            ];
        $this->assertArraySubset($expectedQualifications,$qualifications);
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());
    }


    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGADSAmSilverYouthCouple()
    {

        $contact=$this->generateContact('Mark','Garber');
        /** @var Workarea $workarea */
        $workarea=$contact->getWorkarea()->first();

        /**
         * @var Participant $leader
         * @var Participant $follower
         */
        list($leader,$follower) = $this->leaderFollower(
            $workarea,'Georgia DanceSport Amateur','Standard',
            'Bronze','Silver',18,14);

        $this->assertArraySubset(
            ['id'=>1,'sex'=>'M','years'=>18,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['Georgia DanceSport Amateur'], 'genreProficiency'=>['Standard'=>'Bronze']],
            $leader->describe());
        $this->assertArraySubset(
            ['id'=>2,'sex'=>'F','years'=>14,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['Georgia DanceSport Amateur'], 'genreProficiency'=>['Standard'=>'Silver']],
            $follower->describe());


        /** @var IfacePlayer $createdPlayer */
        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $leader,$follower);
        /** @var IfacePlayer $retrievedPlayer */
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());

        $description=$retrievedPlayer->describe();
        $qualifications = $description['qualifications'];

        $expectedQualifications
            =
            ['Georgia DanceSport Amateur' =>
                [
                    'Standard' =>
                        [
                            'genre' => "Standard",
                            'proficiency' =>"Silver",
                            'age' =>"Youth",
                            'type' =>"Amateur"
                        ]
                ]
            ];
        $this->assertArraySubset($expectedQualifications,$qualifications);
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());
    }



    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGADSAmNoviceSenior4SmoothCouple()
    {

        $contact=$this->generateContact('Mark','Garber');
        /** @var Workarea $workarea */
        $workarea=$contact->getWorkarea()->first();

        /**
         * @var Participant $leader
         * @var Participant $follower
         */
        list($leader,$follower) = $this->leaderFollower(
            $workarea,'Georgia DanceSport Amateur','Smooth',
            'Novice','Silver',65,60);

        $this->assertArraySubset(
            ['id'=>1,'sex'=>'M','years'=>65,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['Georgia DanceSport Amateur'], 'genreProficiency'=>['Smooth'=>'Novice']],
            $leader->describe());
        $this->assertArraySubset(
            ['id'=>2,'sex'=>'F','years'=>60,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['Georgia DanceSport Amateur'], 'genreProficiency'=>['Smooth'=>'Silver']],
            $follower->describe());


        /** @var IfacePlayer $createdPlayer */
        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $leader,$follower);
        /** @var IfacePlayer $retrievedPlayer */
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());

        $description=$retrievedPlayer->describe();
        $qualifications = $description['qualifications'];

        $expectedQualifications
            =
            ['Georgia DanceSport Amateur' =>
                [
                    'Smooth' =>
                        [
                            'genre' => "Smooth",
                            'proficiency' =>"Novice",
                            'age' =>"Senior 4",
                            'type' =>"Amateur"
                        ]
                ]
            ];
        $this->assertArraySubset($expectedQualifications,$qualifications);
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGADSAmSenior1ChampionshipCouple()
    {

        $contact=$this->generateContact('Mark','Garber');
        /** @var Workarea $workarea */
        $workarea=$contact->getWorkarea()->first();

        /**
         * @var Participant $leader
         * @var Participant $follower
         */

        list($leader,$follower) = $this->leaderFollower(
            $workarea,'Georgia DanceSport Amateur','Smooth',
            'Championship','Pre Championship',45,30);

        $this->assertArraySubset(
            ['id'=>1,'sex'=>'M','years'=>45,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['Georgia DanceSport Amateur'], 'genreProficiency'=>['Smooth'=>'Championship']],
            $leader->describe());
        $this->assertArraySubset(
            ['id'=>2,'sex'=>'F','years'=>30,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['Georgia DanceSport Amateur'], 'genreProficiency'=>['Smooth'=>'Pre Championship']],
            $follower->describe());


        /** @var IfacePlayer $createdPlayer */
        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $leader,$follower);
        /** @var IfacePlayer $retrievedPlayer */
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());

        $description=$retrievedPlayer->describe();
        $qualifications = $description['qualifications'];

        $expectedQualifications
            =
            ['Georgia DanceSport Amateur' =>
                [
                    'Smooth' =>
                        [
                            'genre' => "Smooth",
                            'proficiency' =>"Championship",
                            'age' =>"Senior 1",
                            'type' =>"Amateur"
                        ]
                ]
            ];
        $this->assertArraySubset($expectedQualifications,$qualifications);
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());
    }


    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     */
    public function testGADSProAmAmateurTeacherSilverPreteen()
    {
        /** @var Contact $contact */
        $contact=$this->generateContact('Mark','Garber');
        /** @var Workarea $workarea */
        $workarea=$contact->getWorkarea()->first();


        $teacher = $this->generateParticipant('Georgia DanceSport ProAm','Smooth','Pre Championship',
            30,'M','Amateur','Teacher');
        $student = $this->generateParticipant('Georgia DanceSport ProAm','Smooth','Pre Silver',
            10,'F','Amateur','Student');
        self::$ifaceParticipantRepository->save($workarea,$teacher);
        self::$ifaceParticipantRepository->save($workarea, $student);
        $this->assertArraySubset(
            ['id'=>1,'sex'=>'M','years'=>30,'typeA'=>'Amateur','typeB'=>'Teacher',
                'models'=>['Georgia DanceSport ProAm'], 'genreProficiency'=>['Smooth'=>'Pre Championship']],
            $teacher->describe());
        $this->assertArraySubset(
            ['id'=>2,'sex'=>'F','years'=>10,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['Georgia DanceSport ProAm'], 'genreProficiency'=>['Smooth'=>'Pre Silver']],
            $student->describe());


        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $teacher, $student);
        /** @var IfacePlayer $retrievedPlayer */
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());

        $description=$retrievedPlayer->describe();
        $qualifications = $description['qualifications'];

        $expectedQualifications
            =
            ['Georgia DanceSport ProAm' =>
                [
                    'Smooth' =>
                        [
                            'genre' => "Smooth",
                            'proficiency' =>"Pre Silver",
                            'age' =>"Preteen 2",
                            'type' =>"Teacher-Student"
                        ]
                ]
            ];
        $this->assertArraySubset($expectedQualifications,$qualifications);
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());

    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\ClassifyException
     */
    public function testGADSProAmProTeacherOpenGoldSenior4()
    {
        /** @var Contact $contact */
        $contact=$this->generateContact('Mark','Garber');
        /** @var Workarea $workarea */
        $workarea=$contact->getWorkarea()->first();


        $teacher = $this->generateParticipant('Georgia DanceSport ProAm','Rhythm','Professional',
            30,'M','Amateur','Teacher');
        $student = $this->generateParticipant('Georgia DanceSport ProAm','Rhythm','Open Gold',
            64,'F','Amateur','Student');
        self::$ifaceParticipantRepository->save($workarea,$teacher);
        self::$ifaceParticipantRepository->save($workarea, $student);
        $this->assertArraySubset(
            ['id'=>1,'sex'=>'M','years'=>30,'typeA'=>'Amateur','typeB'=>'Teacher',
                'models'=>['Georgia DanceSport ProAm'], 'genreProficiency'=>['Rhythm'=>'Professional']],
            $teacher->describe());
        $this->assertArraySubset(
            ['id'=>2,'sex'=>'F','years'=>64,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['Georgia DanceSport ProAm'], 'genreProficiency'=>['Rhythm'=>'Open Gold']],
            $student->describe());


        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $teacher, $student);
        /** @var IfacePlayer $retrievedPlayer */
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());

        $description=$retrievedPlayer->describe();
        $qualifications = $description['qualifications'];

        $expectedQualifications
            =
            ['Georgia DanceSport ProAm' =>
                [
                    'Rhythm' =>
                        [
                            'genre' => "Rhythm",
                            'proficiency' =>"Open Gold",
                            'age' =>"Senior 3",
                            'type' =>"Teacher-Student"
                        ]
                ]
            ];
        $this->assertArraySubset($expectedQualifications,$qualifications);
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());
    }

}