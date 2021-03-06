<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/2/18
 * Time: 4:57 PM
 */

namespace Tests\Command;

use App\Command\ModelDefinition;
use App\Command\ModelRebuild;
use App\Doctrine\Model\PrimitivesBuilder;
use App\Entity\Configuration\Miscellaneous;
use App\Entity\Configuration\Model;
use App\Entity\Models\Domain;
use App\Entity\Models\Tag;
use App\Entity\Models\Value;
use App\Subscriber\CommandStatusSubscriber;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;

class DefinitionBuilderTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private static $entityManagerModels;

    /** @var EntityManagerInterface */
    private static $entityManagerConfiguration;

    /** @var \App\Kernel */
    protected static $kernel;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
        // TODO: Remove next line when environment variables are set up make conditional for production
        (new Dotenv())->load(__DIR__.'/../../.env');
        // TODO: Change dev->prod, false for production
    }


    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\GeneralException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp()
    {
        self::$kernel = self::bootKernel();
        self::$entityManagerModels = self::$kernel->getContainer()->get('doctrine.orm.models_entity_manager');
        self::$entityManagerConfiguration = self::$kernel->getContainer()->get('doctrine.orm.configuration_entity_manager');
        /** @var /App/Repository/Models/DomainRepository $domainRepository */
        /** @var /App/Repository/Models/ValueRepository $valueRepository   */
        /** @var /App/Repository/Models/TagRepository $tagRepository       */
        /** @var /App/Repository/Configuration/MiscellaneousRepository $miscellaneousRepository */
        $purgerModels=new ORMPurger(self::$entityManagerModels);
        $purgerConfiguration = new ORMPurger(self::$entityManagerConfiguration);
        $purgerModels->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purgerConfiguration->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        /** @var /Doctrine/DBAL/Connection $connectionModel */
        $connectionModel=$purgerModels->getObjectManager()->getConnection();
        /** @var /Doctrine/DBAL/Connection $connectionConfiguration */
        $connectionConfiguration=$purgerConfiguration->getObjectManager()->getConnection();
        $connectionModel->query( 'SET FOREIGN_KEY_CHECKS=0' );
        $connectionConfiguration->query('SET FOREIGN_KEY_CHECKS=0');
        $purgerModels->purge();
        $purgerConfiguration->purge();
        $connectionModel->query('SET FOREIGN_KEY_CHECKS=1');
        $connectionConfiguration->query('SET FOREIGN_KEY_CHECKS=1');
        self::$entityManagerModels->clear();
        self::$entityManagerConfiguration->clear();
        $emModels=self::$entityManagerModels;

        $domainRepository = $emModels->getRepository(Domain::class);
        $valueRepository  = $emModels->getRepository(Value::class);
        $tagRepository    = $emModels->getRepository(Tag::class);
        $emConfiguration=self::$entityManagerConfiguration;
        $miscellaneousRepository = $emConfiguration->getRepository(Miscellaneous::class);

        /** @var PrimitivesBuilder $primitivesBuilder */
        $primitivesBuilder = new PrimitivesBuilder($domainRepository,
                                                    $valueRepository,
                                                    $tagRepository,
                                                    $miscellaneousRepository);
        $yamlTxt=file_get_contents(__DIR__.'/../Scripts/Yaml/Model/primitives.yml');
        /** @var TraceableEventDispatcher $eventDispatcher*/
        $eventDispatcher = self::$kernel->getContainer()->get('event_dispatcher');
        $subscriber = new CommandStatusSubscriber();

        $eventDispatcher->addSubscriber($subscriber);
        $primitivesBuilder->build( $yamlTxt );
    }



    private function commandTestBuild($testSource)
    {
        $application = new Application('Command Test Build');
        $command = new ModelDefinition();
        $application->add($command);
        $commandTester = new CommandTester($command);
        $executionItem=[
            'command'=>$command->getName(),
            'filename'=>__DIR__."/../Scripts/Yaml/Model/$testSource"
        ];
        $commandTester->execute($executionItem);
        $output=$commandTester->getDisplay();
        return $output;
    }

    private function commandTestRebuild()
    {
        $application = new Application('Command Test Build');
        $command = new ModelRebuild();
        $application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'=>$command->getName()
        ]);
        $output=$commandTester->getDisplay();
        return $output;
    }

    public function testCommandLoadModelDefinitionError()
    {
        $output = $this->commandTestBuild('model-1308-exception-dance.yml');
        $message ='"not_dance" at row:54, col:25 is an invalid dance.';
        $this->assertContains($message, $output);
    }

    public function testCommandLoadModelDefinitionCorrect()
    {
        $output=$this->commandTestBuild('model-correct-amateur.yml');
        $this->assertContains('Commencing at', $output);
        $this->assertContains('100%', $output);
        $this->assertContains('Duration',$output);
    }


    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function prepRebuild()
    {
        $emConfig=self::$entityManagerConfiguration;
        $repositoryModels=$emConfig->getRepository(Model::class);
        $definitionYaml=file_get_contents(__DIR__.'/../Scripts/Yaml/Model/model-correct-amateur.yml');
        $model=new Model();
        $model->setModelId(1)
                ->setName('Georgia DanceSport')
                ->setText($definitionYaml)
                ->setUpdatedAt(new \DateTime('now'));
        $emModel=$repositoryModels->getEntityManager();
        $emModel->persist($model);
        $emModel->flush();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testRebuild()
    {
        $this->prepRebuild();
        $output=$this->commandTestRebuild();
        $this->assertContains('Models rebuilt successfully.',$output);
    }

}