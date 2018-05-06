<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/9/17
 * Time: 10:42 PM
 */

namespace App\DataFixtures\ORM;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\Faker\GeneratorFactory;

class LoadFixtures extends Fixture
{
    public function load(ObjectManager $manager) {
       $loader= new NativeLoader();
       $objectSet = $loader->loadFile(__DIR__.'/fixtures.yml');
       foreach($objectSet->getObjects() as $object){
           $manager->persist($object);
       }
       $manager->flush();
    }

  /*public function load(ObjectManager $manager)
   {

   }*/

}