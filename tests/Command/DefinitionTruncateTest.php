<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/23/18
 * Time: 11:13 PM
 */

namespace App\Tests\Command;



use App\Command\ModelTruncate;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Dotenv\Dotenv;

class DefinitionTruncateTest extends KernelTestCase
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
        // TODO: Remove next line when environment variables are set up make conditional for production
        (new Dotenv())->load(__DIR__.'/../../.env');
        // TODO: Change dev->prod, false for production
    }

    private function commandTestTruncate($parameter){
        $application = new Application("Test Truncate");
        $command = new ModelTruncate();
        $application->add($command);
        $commandTester = new CommandTester($command);
        $executionItem = [
            'command'=>$command->getName(),
            'parameter'=>$parameter];
        $commandTester->execute($executionItem);
        $output=$commandTester->getDisplay();
        return $output;
    }

    public function testCommandTruncateModels()
    {
        $output=$this->commandTestTruncate('Models');
        $this->assertContains('Truncate complete',$output);
    }

    public function testCommandTruncateAll()
    {
        $output=$this->commandTestTruncate('all');
        $this->assertContains('Truncate complete',$output);
    }

}