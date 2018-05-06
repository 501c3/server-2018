<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 2/21/18
 * Time: 10:45 PM
 */

namespace App\Command;


use App\Doctrine\Model\PrimitivesBuilder;
use App\Entity\Configuration\Miscellaneous;
use App\Entity\Models\Domain;
use App\Entity\Models\Tag;
use App\Entity\Models\Value;
use App\Kernel;
use App\Repository\Configuration\MiscellaneousRepository;
use App\Repository\Models\DomainRepository;
use App\Repository\Models\ValueRepository;
use App\Subscriber\CommandStatusSubscriber;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;


/**
 * Class ModelPrimitives
 * @package App\Command
 */
class ModelPrimitives extends Command
{
    /**
     * @var PrimitivesBuilder
     */
    private $primitivesBuilder;

    /** @var EntityManager */
    private $entityManagerModels;

    /** @var EntityManager */
    private $entityManagerConfiguration;

    /** @var CommandStatusSubscriber */
    private $subscriber;

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
        /** @var DomainRepository $repositoryDomain */
        $repositoryDomain=$emModels->getRepository(Domain::class);
        /** @var ValueRepository $repositoryValue */
        $repositoryValue=$emModels->getRepository(Value::class);
        /** @var \App\Repository\Models\TagRepository $repositoryTag */
        $repositoryTag=$emModels->getRepository(Tag::class);
        /** @var  MiscellaneousRepository $repositoryMisc */
        $repositoryMisc=$emConfig->getRepository(Miscellaneous::class);
        $this->primitivesBuilder = new PrimitivesBuilder($repositoryDomain,
                                                         $repositoryValue,
                                                         $repositoryTag,
                                                         $repositoryMisc,
                                                         $dispatcher);

    }

    protected function configure(){
        $this->setName('model:load:primitives')
            ->setDescription('Parse yaml primitive components prior to specifying a competition model')
            ->addArgument(  'filename',
                            InputArgument::REQUIRED,
                            "A valid path/filename must be specified")
            ->setHelp('You must specify a valid yaml component specification');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->subscriber->setOutputInterface($output);
        $yamlTxt=file_get_contents($input->getArgument('filename'));
        try{
            $this->primitivesBuilder->build($yamlTxt);
        } catch (\Exception $e){
            $output->writeln($e->getMessage());
        }

    }

}