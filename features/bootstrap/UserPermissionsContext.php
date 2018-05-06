<?php

use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\MinkExtension\Context\MinkContext;
use App\Kernel;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 2/21/18
 * Time: 6:30 PM
 */

class UserPermissionsContext extends MinkContext implements MinkAwareContext
{


    /**
     * @var KernelInterface
     */
    private static $kernel;

    /**
     * @BeforeSuite
     */
    public static function bootstrapSymfony()
    {
        require __DIR__.'/../../vendor/autoload.php';
        require __DIR__.'/../../src/Kernel.php';
        self::$kernel =new Kernel('test',true);
        self::$kernel->boot();
    }


    /**
     * @param KernelInterface $kernel
     * @throws Exception
     */
    public static function loadFixtures(KernelInterface $kernel)
    {
        $application = new Application($kernel);
        $input = new ArrayInput(['command'=>'doctrine:fixtures:load',
                                 '--em'=>'access',
                                 '--no-interaction']);
        $output= new BufferedOutput();
        $application->run($input,$output);
    }

    public static function clearData()
    {
        $container = self::$kernel->getContainer();
        $em = $container
            ->get('doctrine')
            ->getManager('access');
        $purger = new ORMPurger($em);
        $purger->purge();
    }


    /**
     * @Given users and superadmin are loaded
     */
    public function usersAndSuperadminAreLoaded()
    {
        return true;
        //self::clearData();
        //self::loadFixtures(self::$kernel);
    }
}