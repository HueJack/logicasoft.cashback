<?php

/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace Logicasoft\Cashback;

use Bitrix\Main\Event;

class UserCashbackHandler
{
    public static function addCashback(float $cashbackValue, string $currency, int $userId, int $orderId)
    {
        $saleUserAccount = \CSaleUserAccount::GetByUserID($userId, $currency);

        //Сразу не будем устанавливать сумму, иначе счет создастся, но без транзакции
        if (empty($saleUserAccount)) {
            \CSaleUserAccount::Add(
                [
                    'USER_ID' => $userId,
                    'CURRENCY' => $currency,
                    'NOTE' => 'Кэшбек'
                ]
            );
        }

        \CSaleUserAccount::UpdateAccount(
            $userId,
            $cashbackValue,
            $currency,
            'Кэшбек',
            $orderId
        );

        $event = new Event(
            'logicasoft.cashback',
            'onAfterCashbackAdd',
            [
                'CASHBACK_AMOUNT' => $cashbackValue,
                'CURRENCY' => $currency,
                'ORDER_ID' => $orderId,
                'USER_ID' => $userId,
            ]
        );
        $event->send();
    }

    public static function rollbackCashback(int $userId, int $orderId, string $currency)
    {
        $transactIterator = \CSaleUserTransact::GetList(
            [],
            [
                'USER_ID' => $userId,
                'ORDER_ID' => $orderId,
                'DEBIT' => 'Y',
                '!NOTES' => 'CANCELED'
            ],
            false,
            false,
            [
                'ID',
                'AMOUNT'
            ]
        );
        $cashbackValue = 0;
        while ($item = $transactIterator->Fetch()) {
            //Будем сразу добавлять и обновлять, чтобы в случае выключенного света была минимальная потеря
            \CSaleUserAccount::UpdateAccount(
                $userId,
                -(float)$item['AMOUNT'],
                $currency,
                'Отмена кэшбека',
                $orderId
            );
            \CSaleUserTransact::Update(
                $item['ID'],
                [
                    'NOTES' => 'CANCELED'
                ]
            );

            $cashbackValue += $item['AMOUNT'];
        }

        $event = new Event(
            'logicasoft.cashback',
            'onAfterCashbackRollback',
            [
                'CASHBACK_AMOUNT' => $cashbackValue,
                'CURRENCY' => $currency,
                'ORDER_ID' => $orderId,
                'USER_ID' => $userId
            ]
        );
        $event->send();
    }
}
