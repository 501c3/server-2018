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

    /** @var PlayerRepository */
    private static $ifacePlayerRepository;

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


        /** @var IfacePlayerRepository ifacePlayerRepository */
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
                                        self::$workareaRepository,                                        self::$formRepository,
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
     * @throws MissingException var_dump(self::$modelsByName);die;
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
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testContactISTDParticipantCouple()
    {
        $repository = self::$formRepository;
        $contact=$this->generateContact('Mark','Garber');
        $workarea=$contact->getWorkarea()->first();
        /**
         * @var Participant $leader
         * @var Participant $follower
         */
        list($leader,$follower)
            =$this->generateSaveCouple($workarea,
                           'ISTD Medal Exams',
                           'Latin',
                   'Pre Bronze',
                        5,
                  'Pre Bronze',
                      4);

        $cnt=$repository->createQueryBuilder('form')
                        ->select('count(form.id)')
                        ->getQuery()
                        ->getSingleScalarResult();
        $this->assertEquals(2, $cnt);
        $this->assertArraySubset(
            ['id'=>1,'first'=>"M5",'last'=>'Latin-Pre Bronze',
                'sex'=>'M','years'=>5,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams']],$leader->describe());
        $this->assertArraySubset(
            ['id'=>2,'first'=>"F4",'last'=>'Latin-Pre Bronze',
                'sex'=>'F','years'=>4,'typeA'=>'Amateur','typeB'=>'Student',
                'models'=>['ISTD Medal Exams']],$follower->describe());

        $createdPlayer=self::$ifacePlayerRepository->createAux($workarea, $leader,$follower);
        $retrievedPlayer=self::$ifacePlayerRepository->read($createdPlayer->getId());
        $this->assertArraySubset($createdPlayer->toArray(),$retrievedPlayer->toArray());
    }

}