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
## TODO:
- добавить установку с помощью composer
- добавить ссылку на маркетплейс
- добавить события:
    1. перед подсчетом событие со списком товаров с возможностью слияния изменений;
    2. при генерации списка стратегий, чтобы разработчик мог добавить свои решения.


**Created by HueJack for LLC Logicasoft**