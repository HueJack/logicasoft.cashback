<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace Logicasoft\Cashback\Strategy;


class Manager
{
    public static function getList()
    {
        return [
            MarginCalculation::class => MarginCalculation::getTitle(),
            RetailCalculation::class => RetailCalculation::getTitle()
        ];
    }
}