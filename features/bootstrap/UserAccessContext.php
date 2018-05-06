<?php

use App\Entity\Access\User;
use App\Kernel;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Testwork\Tester\Result\TestResult;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Defines application features from the specific context.
 */

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__.'/../../.env');

class UserAccessContext extends MinkContext implements MinkAwareContext
{
    /**
     * @var KernelInterface
     */
     private static $container;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     *
     */
    public function __construct(){}

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
        self::clearData();
    }




    /**
     * @BeforeScenario
     */
    public static function clearData()
    {
        $em = self::$container
                    ->get('doctrine')
                    ->getManager('access');
        $purger = new ORMPurger($em);
        $purger->purge();
    }


    /**
     * @AfterStep
     *
     * @param AfterStepScope $scope
     */
    public function waitToDebugInBrowserOnStepErrorHook(AfterStepScope $scope)
    {
        if ($scope->getTestResult()->getResultCode() == TestResult::FAILED) {
            echo PHP_EOL . "PAUSING ON FAIL" . PHP_EOL;
            $this->getSession()->wait(10000);
        }
    }


    /**
     * @When I pause to authenticate
     */
    public function iPauseToAuthenticate()
    {
        $this->getSession()->wait(5000);
        #sleep(60);
    }

    /**
     * @Given There is user :first :last :username :email :password :mobile,:enabled, :role
     */
    public function thereIsUser($first, $last, $username, $email, $password, $mobile, $enabled, $role)
    {
        $user = new User();
        $enabled = $enabled=="true"?true:false;
        $user->setFirst($first)
            ->setLast($last)
            ->setUsername($username)
            ->setEmail($email)
            ->setPlainPassword($password)
            ->setMobile($mobile)
            ->setEnabled($enabled)
            ->setRoles([$role]);
        $em=self::$container->get('doctrine')->getManager('access');
        $em->persist($user);
        $em->flush();
    }
}

