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


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompetitionInterfaceBuild extends Command
{
    const DESCRIPTION = 'Interface building command for REST';

    protected function configure()
    {
        $this->setName('competition:interface')
            ->setDescription(self::DESCRIPTION)
            //->addArgument('filename',
            //    InputArgument::REQUIRED,
            //    'path/filename')
            ->setHelp('You must specify a valid path/filename.yaml must be specified');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute( $input, $output ); // TODO: Change the autogenerated stub
    }

}