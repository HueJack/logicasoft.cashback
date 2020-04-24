<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace Logicasoft\Cashback;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Sale\Order;

class EventsHandler
{
    /**
     * Событие срабатывает при изменении статуса заказа
     * $status = F - заказ выполнен
     *
     * @param $orderId
     * @param $status
     */
    public static function calculateCashback($orderId, $status)
    {
        try {
            $order = Order::load($orderId);

            if ($status !== 'F') {
                UserCashbackHandler::rollbackCashback($order->getUserId(), $order->getId(), $order->getCurrency());

                return;
            }

            $strategy = \COption::GetOptionString('logicasoft.cashback', 'CALCULATE_STRATEGY', null);
            if (is_null($strategy)) {
                return;
            }

            $cashbackValue = (new CalculateHandler($order, (new $strategy())))->getCashbackValue();
            if ($cashbackValue > 0) {
                UserCashbackHandler::addCashback(
                    $cashbackValue,
                    $order->getCurrency(),
                    $order->getUserId(),
                    $order->getId()
                );
            }
        } catch (\Exception $e) {
            static::handleError($e->getMessage());
        }
    }

    /**
     * Функция вызывается в момент начисления кэшбека в UserCashbackHandler::addCashback();
     *
     * @param Event $event
     * @throws \Exception
     */
    public static function sendNotifyMessages(Event $event)
    {
        $params = $event->getParameters();
        $messageNotify = new MessageNotifyHandler(
            (float)$params['CASHBACK_AMOUNT'],
            (string)$params['CURRENCY'],
            (int)$params['ORDER_ID'],
            (int)$params['USER_ID']
        );

        $isSendEmail = Option::get(
            'logicasoft.cashback',
            'SEND_EMAIL_NOTIFICATION_ABOUT_ADDED_CASHBACK',
            0
        );
        if ($isSendEmail) {
            $messageNotify->sendEmail();
        }

        $isSendSms = Option::get(
            'logicasoft.cashback',
            'SEND_SMS_NOTIFICATION_ABOUT_ADDED_CASHBACK',
            0
        );
        if ($isSendSms) {
            $messageNotify->sendSms();
        }
    }

    protected static function handleError(string $errorMessage)
    {
        \CAdminNotify::Add([
            'TAG' => 'calculate_error',
            'MESSAGE' => $errorMessage,
            'MODULE_ID' => 'logicasoft.cashback',
            'ENABLE_CLOSE' => 'Y'
        ]);
    }
}
