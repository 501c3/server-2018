<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SystemBuild extends Command
{
    protected static $defaultName = 'system:build';


    protected function configure()
    {
        $this
            ->setDescription('Rebuild entire system from scratch')
            ->addArgument('filename', InputArgument::REQUIRED, 'Argument description')
            ->setHelp('A master yaml file must be specified')
        ;
    }

    /**
     * @param string $name
     * @param array $parameters
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    private function runSubcommand(string $name, array $parameters, OutputInterface $output)
    {
        return $this->getApplication()->find($name)->run(new ArrayInput($parameters), $output);
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('filename');
        $contents=file_get_contents($filename);
        $this->runSubcommand('competition:truncate',[],$output);
        $this->runSubcommand('model:truncate',['parameter'=>'all'],$output);
        $this->runSubcommand('sales:truncate',[],$output);
        $this->runSubcommand('configuration:truncate',[],$output);
        $yaml = Yaml::parse($contents);
        $io->writeln('Loading Primitives');
        $this->runSubcommand('model:load:primitives',['filename'=>$yaml['primitives']],$output);
        foreach($yaml['models'] as $filename){
            $io->writeln('Loading model:'.$filename);
            $this->runSubcommand('model:load:definition',['filename'=>$filename],$output);
        }
        $io->writeln('Loading Sales Channel : '.$yaml['sales']);
        $this->runSubcommand('sales:channel:load',['filename'=>$yaml['sales']],$output);
        $io->writeln('Building Sequence : '.$yaml['competition']['sequence']);
        $this->runSubcommand('competition:sequence',['filename'=>$yaml['competition']['sequence']],$output);
        $io->success('All databases rebuilt.');
    }
}
