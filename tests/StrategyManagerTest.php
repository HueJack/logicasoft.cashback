<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace LLC\Tests;

use Logicasoft\Cashback\Strategy\Manager;
use Logicasoft\Cashback\Strategy\MarginCalculation;
use Logicasoft\Cashback\Strategy\RetailCalculation;

class StrategyManagerTest extends \PHPUnit_Framework_TestCase
{
    private $manager;

    public function setUp()
    {
        parent::setUp();

        $this->manager = Manager::getInstance();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider addStrategyExceptionProvider
     */
    public function testAddStrategyException($className)
    {
        $this->manager->addStrategy($className);
    }

    public function addStrategyExceptionProvider()
    {
        return [
            [\stdClass::class],
            ['DontExistsClass'],
            ['333223sdf'],
        ];
    }

    public function testGetList()
    {
        $this->manager->addStrategy(
            MarginCalculation::class
        );
        $this->manager->addStrategy(
            RetailCalculation::class
        );

        $this->assertEquals(
            $this->manager::getList(),
            [
                MarginCalculation::class => MarginCalculation::getTitle(),
                RetailCalculation::class => RetailCalculation::getTitle(),
            ]
        );
    }
}
