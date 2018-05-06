<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/23/18
 * Time: 3:57 PM
 */

namespace App\Command;


use App\Doctrine\Sales\ChannelBuilder;
use App\Entity\Configuration\Sales;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Inventory;
use App\Entity\Sales\Parameters;
use App\Entity\Sales\Pricing;
use App\Entity\Sales\Processor;
use App\Entity\Sales\Settings;
use App\Entity\Sales\Tag;
use App\Kernel;
use App\Repository\Configuration\SalesRepository;

use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\InventoryRepository;
use App\Repository\Sales\ParametersRepository;
use App\Repository\Sales\PricingRepository;
use App\Repository\Sales\ProcessorRepository;
use App\Repository\Sales\SettingsRepository;
use App\Repository\Sales\TagRepository;
use App\Subscriber\CommandStatusSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

class SalesChannel extends Command
{
    /** @var EntityManagerInterface */
    private $entityManagerSales;

    /** @var ChannelInventory */
    private $channelBuilder;

    /** @var CommandStatusSubscriber */
    private $subscriber;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $kernel = new Kernel('dev', true );
        $kernel->boot();
        $container = $kernel->getContainer();
        $this->subscriber=$container->get("app.command.subscriber");
        /** @var TraceableEventDispatcher $dispatcher */
        $dispatcher = $container->get('event_dispatcher');
        $dispatcher->addSubscriber($this->subscriber);
        /** @var EntityManagerInterface $emSales */
        $emSales = $container->get( 'doctrine.orm.sales_entity_manager' );
        /** @var EntityManagerInterface $emConfig */
        $emConfig= $container->get('doctrine.orm.configuration_entity_manager');
        /** @var ChannelRepository $channelRepository */
        $channelRepository = $emSales->getRepository(Channel::class);
        /** @var InventoryRepository $inventoryRepository */
        $inventoryRepository = $emSales->getRepository(Inventory::class);
        /** @var PricingRepository $pricingRepository */
        $pricingRepository = $emSales->getRepository(Pricing::class);
        /** @var ParametersRepository $parameterRepository */
        $parameterRepository = $emSales->getRepository(Parameters::class);
        /** @var TagRepository $tagRepository */
        $tagRepository = $emSales->getRepository(Tag::class);
        /** @var ProcessorRepository $processorRepository */
        $processorRepository = $emSales->getRepository(Processor::class);
        /** @var SettingsRepository $settingsRepository */
        $settingsRepository = $emSales->getRepository(Settings::class);
        /** @var SalesRepository $salesRepository */
        $salesRepository = $emConfig->getRepository(Sales::class);
        $this->channelBuilder = new ChannelBuilder(
            $channelRepository,
            $inventoryRepository,
            $pricingRepository,
            $parameterRepository,
            $tagRepository,
            $processorRepository,
            $settingsRepository,
            $salesRepository,
            $dispatcher);
        $this->entityManagerSales=$emSales;
    }

    public function configure()
    {
        $this->setName('sales:channel:load')
            ->setDescription('Load sales channel information.')
            ->addArgument(  'filename',
                InputArgument::REQUIRED,
                "A valid filename must be specified")
            ->setHelp('You must specify a valid Yaml file which specifies sales channel and inventory.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->subscriber->setOutputInterface($output);
        $yamlTxt=file_get_contents($input->getArgument('filename'));
        try{
            $connection = $this->entityManagerSales->getConnection();
            $connection->beginTransaction();
            try{
                $this->channelBuilder->build($yamlTxt);
                $connection->commit();
            } catch( \Exception $e) {
                $connection->rollback();
                throw $e;
            }
        } catch(\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }

}