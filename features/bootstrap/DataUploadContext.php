<?php
use App\Kernel;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;


/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 2/24/18
 * Time: 11:36 AM
 */

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__.'/../../.env');

class DataUploadContext extends MinkContext implements MinkAwareContext
{
    private $output;

    /**
     * @var KernelInterface
     */
    private static $container;

    /**
     * @BeforeSuite
     */

    public static function bootstrapSymfony()
    {
        require __DIR__.'/../../vendor/autoload.php';
        require __DIR__.'/../../src/Kernel.php';
        $kernel = new Kernel('test',true);
        $kernel->boot();
        self::$container = $kernel->getContainer();
        self::clearData('access');
        self::clearData('models');
        self::clearData('configuration');
        self::clearData('sales');
    }

    /* TODO: eliminate this annotation
     * @BeforeScenario
     */
    public static function clearData($database)
    {
        $em = self::$container
            ->get('doctrine')
            ->getManager($database);
        $purger = new ORMPurger($em);
        $purger->purge();
    }

    /**
     * @Given There is a file name :filename
     */
    public function thereIsAFileName($filename)
    {
        $output = shell_exec('ls ../scripts');
        die('output is :'.$output);
        return strpos($output,$filename);
    }

     /**
     * @When I run  :script
     */
    public function iRun($script)
    {
        $this->output=shell_exec($script);
    }

}