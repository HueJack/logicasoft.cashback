[![Build Status](https://travis-ci.org/HueJack/logicasoft.cashback.svg?branch=master)](https://travis-ci.org/HueJack/logicasoft.cashback)

# Битрикс: кэшбек с покупок

Добавляет возможность возвращать кэшбек на внутренний счет пользователя с выполненного заказа. 
После начисления заказа отправляются уведомления на email и телефон указанные в заказе. 
Размер кэшбека зависит от настроек: % кэшбека и стратегии подсчета.

## Установка
Скопировать в папку /local/modules/ или /bitrix/modules/.

Ожидаем модерации в маркетплейс

## Стратегии подсчета

1. Расчет от маржинальности товара: (розничная цена - закупочная цена) * количество * размер кэшбека%
2. Расчет от розничной цены: розничная * количество * размер кэшбека% 

## Уведомления
Шаблоны уведомлений доступны в разделе *Почтовые и СМС события*. Тип события *LOGICASOFT_CASHBACK_ADD_EMAIL*.

## Настройка
Страница настройки доступна по адресу *Настройки/Настройка продукта/Настройки модулей/Кэшбек с покупок*.

## События
### После добавлении кэшбека onAfterCashbackAdd
В параметрах события передаются следующие данные:
- CASHBACK_AMOUNT - размер кэшбека
- CURRENCY - валюта
- ORDER_ID - id заказа
- USER_ID - id пользователя
#### Использование
```php
\Bitrix\Main\EventManager::getInstance()->addEventHandler(
  'logicasoft.cashback',
  'onAfterCashbackAdd', 
  [
    'Class',
    'Method'
  ]
);

Class {
    public static function Method(\Bitrix\Main\Event $event) 
    {
        //Будет содержать массив описанных выше данных
        $parameters = $event->getParameters();
    }
}
```

###При добавлении стратегий в список onCollectCashbackStrategies
В параметры передается инстанцированный Logicasoft\Cashback\Strategy\Manager;
####Добавление собственной стратегии расчета.
Класс стратегии должен реализовывать интерфейс \Logicasoft\Cashback\Strategy\StrategyInterface
```php
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Logicasoft\Cashback\Strategy\Manager;
use Logicasoft\Cashback\Strategy\RetailCalculation;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler(
    'logicasoft.cashback',
    'onCollectCashbackStrategies',
    'llcCollectCashbackStrategies'
);

function llcCollectCashbackStrategies(Event $event)
{
    /** @var Manager $manager */
    $manager = $event->getParameter('manager');

    $manager->addStrategy(
        RetailCalculation::class
    );
}
```
## TODO:
- добавить установку с помощью composer
- добавить ссылку на маркетплейс
- добавить события:
    1. перед подсчетом событие со списком товаров с возможностью слияния изменений;   


**Created by HueJack for LLC Logicasoft**