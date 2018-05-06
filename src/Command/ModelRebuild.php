<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/23/18
 * Time: 4:00 PM
 */

namespace App\Command;

use App\Doctrine\Model\DefinitionBuilder;
use App\Doctrine\Model\PrimitivesBuilder;
use App\Entity\Configuration\Miscellaneous;
use App\Entity\Configuration\Model as ModelConfiguration;
use App\Entity\Models\Domain;
use App\Entity\Models\Event;
use App\Entity\Models\Model;
use App\Entity\Models\Player;
use App\Entity\Models\Subevent;
use App\Entity\Models\Tag;
use App\Entity\Models\Value;
use App\Exceptions\RebuildException;
use App\Kernel;
use App\Repository\Configuration\MiscellaneousRepository;
use App\Repository\Models\DomainRepository;
use App\Repository\Models\ModelRepository;
use App\Repository\Models\TagRepository;
use App\Repository\Models\ValueRepository;
use App\Subscriber\CommandStatusSubscriber;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

class ModelRebuild extends Command
{

    /** @var EntityManager|object  */
    private $entityManagerModels;

    /** @var EntityManager|object  */
    private $entityManagerConfiguration;

    /** @var PrimitivesBuilder  */
    private $primitivesBuilder;

    /** @var DefinitionBuilder  */
    private $definitionBuilder;

    /** @var CommandStatusSubscriber|object  */
    private $subscriber;

    /** @var MiscellaneousRepository  */
    private $repositoryMiscellaneous;

    /** @var ModelRepository */
    private $repositoryModels;

    /** @var ModelConfiguration */
    private $repositoryDefinition;

    public function __construct(?string $name = null)
    {
        parent::__construct( $name );
        (new Dotenv())->load(__DIR__.'/../../.env');
        // TODO: dev->prod and true->false or add code that will take information from environment.
        $kernel=new Kernel('dev',true);
        $kernel->boot();
        $container = $kernel->getContainer();
        $this->subscriber=$container->get('app.command.subscriber');
        $dispatcher = $container->get('event_dispatcher');
        $dispatcher->addSubscriber($this->subscriber);
        $this->entityManagerModels=$kernel->getContainer()
            ->get('doctrine.orm.models_entity_manager');
        $this->entityManagerConfiguration=$kernel->getContainer()
            ->get('doctrine.orm.configuration_entity_manager');
        $emModels=$this->entityManagerModels;
        $emConfig=$this->entityManagerConfiguration;
        $repositoryModels=$emModels->getRepository(Model::class);
        /** @var DomainRepository $repositoryDomain */
        $repositoryDomain=$emModels->getRepository(Domain::class);
        $repositoryEvent=$emModels->getRepository(Event::class);
        $repositorySubevent=$emModels->getRepository(Subevent::class);
        $repositoryPlayer=$emModels->getRepository(Player::class);
        /** @var ValueRepository $repositoryValue */
        $repositoryValue=$emModels->getRepository(Value::class);
        /** @var TagRepository $repositoryTag */
        $repositoryTag=$emModels->getRepository(Tag::class);
        /** @var MiscellaneousRepository $repositoryMisc */
        $repositoryMisc=$emConfig->getRepository(Miscellaneous::class);
        $this->repositoryDefinition=$emConfig->getRepository(ModelConfiguration::class);
        $this->primitivesBuilder = new PrimitivesBuilder(
            $repositoryDomain,
            $repositoryValue,
            $repositoryTag,
            $repositoryMisc,
            $dispatcher);
        $this->definitionBuilder = new DefinitionBuilder(
            $repositoryModels,
            $repositoryDomain,
            $repositoryEvent,
            $repositorySubevent,
            $repositoryPlayer,
            $repositoryTag,
            $repositoryValue,
            $this->repositoryDefinition,
            $dispatcher);
        $this->repositoryMiscellaneous=$repositoryMisc;
        $this->repositoryModels=$repositoryModels;
    }

    /**
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    private function purge(OutputInterface $output)
    {
        $connection=$this->entityManagerModels->getConnection();
        /** @var MySqlSchemaManager $schemaManager */
        $schemaManager=$connection->getSchemaManager();
        $tables=$schemaManager->listTables();
        $query="SET FOREIGN_KEY_CHECKS = 0;\n";
        foreach($tables as $table){
            $name = $table->getName();
            $query.='TRUNCATE '.$name .";\n";
        }
        $query.="SET FOREIGN_KEY_CHECKS = 1;\n";
        try {
            $connection->beginTransaction();
            $connection->executeQuery( $query, [], [] );
            $connection->commit();
            $output->writeln('Model primitives reloaded. Proceeding to rebuild models...this may take a while!');
        }catch(\Exception $e){
            $connection->rollBack();
            throw $e;
        }
    }


    protected function configure(){
        $this->setName('model:rebuild')
            ->setDescription('Rebuild model database from configuration tables')
            ->setHelp('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->subscriber->setOutputInterface($output);
        try{
            $this->purge($output);
            /** @var Miscellaneous $misc */
            $misc=$this->repositoryMiscellaneous->findOneBy(['name'=>'primitives']);
            if(!$misc){
                throw new RebuildException('Unable to rebuild.  Primitives missing.',
                                            RebuildException::NO_PRIMITIVES);
            }
            $modelDefinitions=$this->repositoryDefinition->findAll();
            if(!count($modelDefinitions)){
                throw new RebuildException('Nothing to rebuild.  No models defined.',
                                            RebuildException::NO_MODELS);
            }
            $this->primitivesBuilder->build($misc->getText());
            /** @var ModelConfiguration $definition */
            foreach($modelDefinitions as $definition){
                $this->definitionBuilder->build($definition->getText());
            }
            $output->writeln('Models rebuilt successfully.');
        } catch (\Exception $e){
            $output->writeln($e->getMessage());
        }
    }
}