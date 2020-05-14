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
### Перед подсчетом суммы кэшбека onBeforeCashbackCalculate
Можно изменить состав списка продуктов, откорректировать значения полей. 
#### Использование
В параметры передается стандартный Bitrix\Main\Event. Список продуктов лежит в параметр basketProducts
```php
\Bitrix\Main\EventManager::getInstance()->addEventHandler(
  'logicasoft.cashback',
  'onBeforeCashbackCalculate', 
  [
    'Class',
    'Method'
  ]
);
Class {
    public static function Method(\Bitrix\Main\Event $event) 
    {
        
        $basketProducts = $event->getParameter('basketProducts');
        
        //Для изменения нужно передать в функцию $result->mofidyFields();
        //массив ключами которого будут id продукта.
        $modifyFields = [];
        foreach ($basketProducts as $id => $item) {
            $modifyFields[$id] = [
                'QUANTITY' => 100, //Изменяем количество продуктов
                'NEW_FIELD' => 'VALUES' //Новое поле в продуктах
            ];
        }
        
        //Добавляем новый продукт в список с id = 100, 
        //нужно учитывать, что в продукте должны быть необходимые для расчета поля
        //Проверка происходит в стратегиях подсчета, к примеру
        //\Logicasoft\Cashback\Strategy\RetailCalculation::checkFields() - ищет поля
        //PRICE и QUANTITY
        $modifyFields['100'] = [
            'QUANTITY_ID' => 500,
            'ID' => 200
        ];
    
        $result = new \Bitrix\Main\Entity\EventResult();
    
        $result->modifyFields($modifyFields);
    
        //Если нужно удалить продукт из списка, то передаем массив с id продуктов в функцию
        //$result->unsetField();
        //Удалим из списка продукт с id=100
        $result->unsetFields([100]);
        
        return $result;
    }
}
```
 

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

### При добавлении стратегий в список onCollectCashbackStrategies
В параметры передается инстанцированный Logicasoft\Cashback\Strategy\Manager;
#### Добавление собственной стратегии расчета.
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

**Created by HueJack for LLC Logicasoft**