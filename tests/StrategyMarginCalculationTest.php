<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace LLC\Tests;


use Logicasoft\Cashback\Strategy\MarginCalculation;
use Logicasoft\Cashback\Strategy\StrategyInterface;

class StrategyMarginCalculationTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $class;

    public function setUp()
    {
        parent::setUp();

        $this->class = MarginCalculation::class;
    }

    public function testTitle()
    {
        $this->assertEquals('Расчет от маржинальности товара', $this->class::getTitle());
    }

    public function testCalculation()
    {
        $this->assertTrue(true);
    }
}
