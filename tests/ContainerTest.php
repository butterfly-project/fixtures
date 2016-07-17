<?php

namespace Butterfly\Component\Fixtures\Tests;

use Butterfly\Component\Config\ConfigBuilder;
use Butterfly\Component\Fixtures\FixtureLoader;
use Doctrine\DBAL\DriverManager;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $builder = ConfigBuilder::createInstance();
        $builder->addPath(__DIR__.'/data/fixtures.yml');

        $config = $builder->getData();

        $connection = DriverManager::getConnection(array(
            'driver'   => 'pdo_pgsql',
            'host'     => 'localhost',
            'dbname'   => 'cigaramobile',
            'user'     => 'agregad',
            'password' => '1',
        ));

        $fixtureLoader = new FixtureLoader($connection);

        $fixtureLoader->load($config);
    }
}
