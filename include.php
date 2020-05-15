<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Logicasoft\Cashback\Strategy\Manager;
use Logicasoft\Cashback\Strategy\MarginCalculation;
use Logicasoft\Cashback\Strategy\RetailCalculation;

$moduleName = 'logicasoft.cashback';

$eventManager = EventManager::getInstance();
$strategyManager = Manager::getInstance();

$eventManager->send(new Event(
    $moduleName,
    'onCollectCashbackStrategies',
    [
        'manager' => $strategyManager
    ]
));

$strategyManager->addStrategy(MarginCalculation::class);
$strategyManager->addStrategy(RetailCalculation::class);