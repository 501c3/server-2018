<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 2/21/18
 * Time: 10:36 PM
 */

namespace App\Command;

use App\Doctrine\Model\DefinitionBuilder;
use App\Entity\Configuration\Model as ModelConfiguration;

use App\Entity\Models\Domain;
use App\Entity\Models\Event;

use App\Entity\Models\Model;
use App\Entity\Models\Player;
use App\Entity\Models\Subevent;
use App\Entity\Models\Tag;
use App\Entity\Models\Value;
use App\Kernel;
use App\Subscriber\CommandStatusSubscriber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

class ModelDefinition extends Command
{
    /** @var  CommandStatusSubscriber*/
    private $subscriber;

    /** @var \Doctrine\ORM\EntityManager|object  */
    private $entityManagerModels;

    /** @var \Doctrine\ORM\EntityManager|object  */
    private $entityManagerConfiguration;

    /** @var DefinitionBuilder */
    private $definitionBuilder;

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
        $this->entityManagerModels=$kernel->getContainer()
            ->get('doctrine.orm.models_entity_manager');
        $this->entityManagerConfiguration=$kernel->getContainer()
            ->get('doctrine.orm.configuration_entity_manager');
        $emModels=$this->entityManagerModels;
        $emConfig=$this->entityManagerConfiguration;
        $repositoryModel=$emModels->getRepository(Model::class);
        $repositoryDomain=$emModels->getRepository(Domain::class);
        $repositoryEvent=$emModels->getRepository(Event::class);
        $repositorySubevent=$emModels->getRepository(Subevent::class);
        $repositoryPlayer=$emModels->getRepository(Player::class);
        $repositoryTag=$emModels->getRepository(Tag::class);
        $repositoryValue=$emModels->getRepository(Value::class);
        $repositoryConfiguration = $emConfig->getRepository( ModelConfiguration::class);
        $this->definitionBuilder=new DefinitionBuilder($repositoryModel,
                                                        $repositoryDomain,
                                                        $repositoryEvent,
                                                        $repositorySubevent,
                                                        $repositoryPlayer,
                                                        $repositoryTag,
                                                        $repositoryValue,
                                                        $repositoryConfiguration,
                                                        $dispatcher);
    }

    protected function configure(){
        $this->setName('model:load:definition')
            ->setDescription('Parse a yaml model specification to the Models database')
            ->addArgument('filename',
                          InputArgument::REQUIRED,
                          "A valid path/filename must be specified")
            ->setHelp('You must specify a valid yaml competition model definition');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->subscriber->setOutputInterface($output);
        $yamlText=file_get_contents($input->getArgument('filename'));
        try {
            $connection = $this->entityManagerModels->getConnection();
            $connection->beginTransaction();
            try{
                $this->definitionBuilder->build( $yamlText );
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