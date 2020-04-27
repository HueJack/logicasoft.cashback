<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace Logicasoft\Cashback\Strategy;


class Manager
{
    protected static $strategies = [];

    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getList()
    {
        return static::$strategies;
    }

    /**
     * @param string $className
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     */
    public function addStrategy(string $className)
    {
        /** @var StrategyInterface $className  */
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf(
                'Error! Class %s is not exists',
                $className
            ));
        }

        $class = new \ReflectionClass($className);

        if (!$class->implementsInterface(StrategyInterface::class)) {
            throw new \InvalidArgumentException(sprintf(
                'Error! Class %s don\'t implements %s',
                $className,
                StrategyInterface::class
            ));
        }

        static::$strategies[$className] = $className::getTitle();
    }
}