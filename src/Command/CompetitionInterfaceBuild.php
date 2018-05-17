<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/9/18
 * Time: 9:36 AM
 */

namespace App\Command;


use App\Doctrine\Competition\InterfaceBuilder;
use App\Entity\Competition\Competition;
use App\Entity\Competition\Iface;
use App\Entity\Models\Domain;
use App\Entity\Models\Model;
use App\Entity\Models\Value;
use App\Kernel;
use App\Subscriber\CommandStatusSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

class CompetitionInterfaceBuild extends Command
{
    const DESCRIPTION = <<<EOD
Parse a yaml REST server specification.  "competition:sequence" must be run prior to executing this command. 
EOD;

    /** @var CommandStatusSubscriber */
    private $subscriber;

    /** @var InterfaceBuilder */
    private $interfaceBuilder;

    /** @var EntityManagerInterface */
    private $entityManagerCompetition;

    public function __construct(?string $name = null)
    {
        parent::__construct( $name );
        (new Dotenv())->load(__DIR__.'/../../.env');
        // TODO: dev->prod and true->false or add code that will take information from environment.
        $kernel = new Kernel('dev',true);
        $kernel->boot();
        $container = $kernel->getContainer();
        $this->subscriber = $container->get("app.command.subscriber");
        $dispatcher = $container->get('event_dispatcher');
        $dispatcher->addSubscriber($this->subscriber);
        $emModels = $kernel->getContainer()->get('doctrine.orm.models_entity_manager');
        $emComp = $kernel->getContainer()->get('doctrine.orm.competition_entity_manager');
        $modelRepository = $emModels->getRepository(Model::class);
        $domainRepository = $emModels->getRepository(Domain::class);
        $valueRepository = $emModels->getRepository(Value::class);
        $competitionRepository = $emComp->getRepository(Competition::class);
        $ifaceRepository = $emComp->getRepository(Iface::class);
        $this->interfaceBuilder = new InterfaceBuilder($modelRepository,
                                                        $domainRepository,
                                                        $valueRepository,
                                                        $competitionRepository,
                                                        $ifaceRepository,
                                                        $dispatcher);
        $this->entityManagerCompetition=$competitionRepository->getEntityManager();
    }

    protected function configure()
    {
        $this->setName('competition:interface')
            ->setDescription(self::DESCRIPTION)
            ->addArgument('filename',
                            InputArgument::REQUIRED,
                        'path/filename')
            ->setHelp('You must specify a valid path/filename.yaml must be specified');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->subscriber->setOutputInterface($output);
        $yamlText = file_get_contents($input->getArgument('filename'));
        try{
            $connection =  $this->entityManagerCompetition->getConnection();
            $connection->beginTransaction();
            try{
                $this->interfaceBuilder->build($yamlText);
                $connection->commit();
            } catch(\Exception $e) {
                $connection->rollBack();
                throw $e;
            }
        } catch (\Exception $e)  {
            $output->writeln($e->getMessage());
        }
    }

}