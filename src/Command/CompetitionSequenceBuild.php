<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/16/18
 * Time: 3:04 PM
 */

namespace App\Command;


use App\Doctrine\Competition\SequenceBuilder;
use App\Entity\Competition\Competition;
use App\Entity\Competition\Event as CompetitionEvent;
use App\Entity\Competition\Model as CompetitionModel;
use App\Entity\Competition\Player as CompetitionPlayer;
use App\Entity\Competition\Schedule;
use App\Entity\Competition\Session;
use App\Entity\Competition\Subevent as CompetitionSubevent;
use App\Entity\Models\Domain;
use App\Entity\Models\Event;
use App\Entity\Models\Model;
use App\Entity\Models\Player;
use App\Entity\Models\Subevent;
use App\Entity\Models\Value;
use App\Kernel;
use App\Repository\Competition\CompetitionRepository;
use App\Repository\Competition\EventRepository as CompetitionEventRepository;
use App\Repository\Competition\ModelRepository;
use App\Repository\Competition\PlayerRepository as CompetitionPlayerRepository;
use App\Repository\Competition\ScheduleRepository;
use App\Repository\Competition\SessionRepository;
use App\Repository\Competition\SubeventRepository as CompetitionSubeventRepository;
use App\Repository\Models\DomainRepository;
use App\Repository\Models\EventRepository;
use App\Repository\Models\PlayerRepository;
use App\Repository\Models\SubeventRepository;
use App\Repository\Models\ValueRepository;
use App\Subscriber\CommandStatusSubscriber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

class CompetitionSequenceBuild extends Command
{
    const DESCRIPTION = <<<EOD
Parse a yaml competition specification to the competition database.  The models used must be previously loaded.
EOD;

   /** @var CommandStatusSubscriber */
   private $subscriber;
   /** @var SequenceBuilder */
   private $sequenceBuilder;
   /** @var \Doctrine\ORM\EntityManager  */
   private $entityManagerCompetition;

   public function __construct(?string $name = null)
   {
       parent::__construct( $name );
       (new Dotenv())->load(__DIR__.'/../../.env');
       // TODO: dev->prod and true->false or add code that will take information from environment.
       $kernel=new Kernel('dev',true);
       $kernel->boot();
       $container = $kernel->getContainer();
       $this->subscriber=$container->get("app.command.subscriber");
       $dispatcher = $container->get('event_dispatcher');
       $dispatcher->addSubscriber($this->subscriber);
       $emModels=$kernel->getContainer()->get('doctrine.orm.models_entity_manager');
       $emComp=$kernel->getContainer()->get('doctrine.orm.competition_entity_manager');
       /** @var Model $modelRepository */
       $modelRepository=$emModels->getRepository(Model::class);
       /** @var DomainRepository $domainRepository */
       $domainRepository=$emModels->getRepository(Domain::class);
       /** @var ValueRepository $valueRepository */
       $valueRepository=$emModels->getRepository(Value::class);
       /** @var PlayerRepository $modelPlayerRepository */
       $modelPlayerRepository=$emModels->getRepository(Player::class);
       /** @var EventRepository $modelEventRepository */
       $modelEventRepository=$emModels->getRepository(Event::class);
       /** @var SubeventRepository $modelSubeventRepository */
       $modelSubeventRepository=$emModels->getRepository(Subevent::class);
       /** @var CompetitionRepository $competitionRepository */
       $competitionRepository=$emComp->getRepository(Competition::class);
       /** @var ModelRepository $competitionModelRepository */
       $competitionModelRepository=$emComp->getRepository( CompetitionModel::class);
       /** @var CompetitionPlayerRepository $competitionPlayerRepository */
       $competitionPlayerRepository=$emComp->getRepository( CompetitionPlayer::class);
       /** @var CompetitionEventRepository $competitionEventRepository */
       $competitionEventRepository=$emComp->getRepository( CompetitionEvent::class);
       /** @var CompetitionSubeventRepository $competitionSubeventRepository */
       $competitionSubeventRepository=$emComp->getRepository( CompetitionSubevent::class);
       /** @var  SessionRepository $competitionSessionRepository */
       $competitionSessionRepository = $emComp->getRepository(Session::class);
       /** @var ScheduleRepository $competitionScheduleRepository */
       $competitionScheduleRepository=$emComp->getRepository(Schedule::class);

       $this->sequenceBuilder=new SequenceBuilder(
                                            $modelRepository,
                                            $domainRepository,
                                            $valueRepository,
                                            $modelPlayerRepository,
                                            $modelEventRepository,
                                            $modelSubeventRepository,
                                            $competitionRepository,
                                            $competitionModelRepository,
                                            $competitionPlayerRepository,
                                            $competitionEventRepository,
                                            $competitionSubeventRepository,
                                            $competitionSessionRepository,
                                            $competitionScheduleRepository,
                                            $dispatcher);
       $this->entityManagerCompetition=$competitionRepository->getEntityManager();
   }

    protected function configure()
   {
       $this->setName('competition:sequence')
            ->setDescription(self::DESCRIPTION)
            ->addArgument('filename',
                          InputArgument::REQUIRED,
                          'path/filename')
           ->setHelp('You must specify a valid path/filename.yaml must be specified');
   }

   protected function execute(InputInterface $input, OutputInterface $output)
   {
       $this->subscriber->setOutputInterface($output);
       $yamlText=file_get_contents($input->getArgument('filename'));
       try {
           $connection = $this->entityManagerCompetition->getConnection();
           $connection->beginTransaction();
           try{
               $this->sequenceBuilder->build( $yamlText );
               $connection->commit();
           } catch (\Exception $e){
               $connection->rollBack();
               throw $e;
           }
       } catch (\Exception $e) {
           $output->writeln($e->getMessage());
       }
   }
}