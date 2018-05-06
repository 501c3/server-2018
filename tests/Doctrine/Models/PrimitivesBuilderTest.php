<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/2/18
 * Time: 5:21 PM
 */

namespace Tests\Doctrine\Models;


use App\Doctrine\Model\PrimitivesBuilder;
use App\Entity\Configuration\Miscellaneous;
use App\Entity\Models\Domain;
use App\Entity\Models\Tag;
use App\Entity\Models\Value;
use App\Repository\Configuration\MiscellaneousRepository;
use App\Repository\Models\DomainRepository;
use App\Repository\Models\TagRepository;
use App\Repository\Models\ValueRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class PrimitivesBuilderTest
 * @package Tests\Doctrine\Models
 */
class PrimitivesBuilderTest extends KernelTestCase
{

    /** @var EntityManagerInterface  */
    private static $entityManagerConfiguration;

    /**  @var EntityManagerInterface  */
    private static $entityManagerModels;

    /** @var PrimitivesBuilder */
    private $primitivesBuilder;


    public static function setUpBeforeClass()
    {
        (new Dotenv())->load(__DIR__.'/../../../.env');
        $kernel = self::bootKernel();
        self::$entityManagerModels = $kernel->getContainer()->get('doctrine.orm.models_entity_manager');
        self::$entityManagerConfiguration = $kernel->getContainer()->get('doctrine.orm.configuration_entity_manager');
    }

    /**
     * @throws DBALException
     */
    protected function setUp()
    {
        $purgerModels=new ORMPurger(self::$entityManagerModels);
        $purgerConfiguration = new ORMPurger(self::$entityManagerConfiguration);
        $purgerModels->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purgerConfiguration->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $connectionModel=$purgerModels->getObjectManager()->getConnection();
        /** @var Connection $connectionConfiguration */
        $connectionConfiguration=$purgerConfiguration->getObjectManager()->getConnection();
        $connectionModel->query( 'SET FOREIGN_KEY_CHECKS=0' );
        $connectionConfiguration->query('SET FOREIGN_KEY_CHECKS=0');
        $purgerModels->purge();
        $purgerConfiguration->purge();
        $connectionModel->query('SET FOREIGN_KEY_CHECKS=1');
        $connectionConfiguration->query('SET FOREIGN_KEY_CHECKS=1');
        /** @var DomainRepository $domainRepository */
        /** @var ValueRepository $valueRepository   */
        /** @var TagRepository $tagRepository       */
        /** @var MiscellaneousRepository $miscellaneousRepository */
        $domainRepository = self::$entityManagerModels->getRepository(Domain::class);
        $valueRepository  = self::$entityManagerModels->getRepository(Value::class);
        $tagRepository    = self::$entityManagerModels->getRepository(Tag::class);
        $miscellaneousRepository = self::$entityManagerConfiguration->getRepository(Miscellaneous::class);
        $this->primitivesBuilder = new PrimitivesBuilder($domainRepository,
                                                         $valueRepository,
                                                         $tagRepository,
                                                         $miscellaneousRepository);
    }

    /**
     * @expectedException \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_key" at row:5, col:6 expected "comment","domain","event-tag".
     * @expectedExceptionCode 1002
     *
     * ModelExceptionCode::PRIMITIVES = 1002
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\GeneralException
     */
    public function test1002ExceptionKey()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Model/primitives-1002-exception-key.yml');
        $this->primitivesBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_domain" at row:11, col:9 .  Expected "style","substyle","proficiency","age","type","tag","dance."
     * @expectedExceptionCode 1004
     *
     * ModelExceptionCode::PRIMITIVE_DOMAINS = 1004
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\GeneralException
     */

    public function test1004ExceptionPrimitiveDomains()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Model/primitives-1004-exception-primitive-domains.yml');
        $this->primitivesBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "redundant_value" at row:13, col:13 is redundantly defined at row:18 col:13.
     * @expectedExceptionCode 1006
     *
     * ModelExceptionCode::REDUNDANT_VALUE = 1006
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\GeneralException
     */
    public function test1006ExceptionRedundantValue(){
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Model/primitives-1006-exception-redundant-value.yml');
        $this->primitivesBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_abbr" at row:11, col:21 expected "abbr".
     * @expectedExceptionCode 1008
     *
     * ModelExceptionCode:ABBR = 1008
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\GeneralException
     */
    public function test1008ExceptionAbbr(){
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Model/primitives-1008-exception-abbr.yml');
        $this->primitivesBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException  \App\Exceptions\GeneralException
     * @expectedExceptionMessage "no_order" at row:11, col:30 expected "order".
     * @expectedExceptionCode 1010
     *
     * ModelExceptionCode::ORDER = 1010
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\GeneralException
     */
    public function test1010ExceptionOrder(){
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Model/primitives-1010-exception-order.yml');
        $this->primitivesBuilder->build( $yamlTxt );
    }

    /**
     * @expectedException   \App\Exceptions\GeneralException
     * @expectedExceptionMessage "redundant_tag" at row:12, col:11 is redundantly defined at row:20 col:11.
     * @expectedExceptionCode 1006
     *
     *
     * ModelExceptionCode::REDUNDANT_VALUE = 1006
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\GeneralException
     */
    public function test1012ExceptionRedundantEventTag(){
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Model/primitives-1012-redundant-event-tag.yml');
        $this->primitivesBuilder->build( $yamlTxt );
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \App\Exceptions\GeneralException
     */
    public function testPrimitivesCorrect()
    {
        $yamlTxt=file_get_contents(__DIR__.'/../../Scripts/Yaml/Model/primitives.yml');
        $result=$this->primitivesBuilder->build($yamlTxt);
        $this->assertTrue($result);
    }
}