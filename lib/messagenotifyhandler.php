<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace Logicasoft\Cashback;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SiteTable;
use Bitrix\Main\UserPhoneAuthTable;
use Bitrix\MessageService\Sender\SmsManager;
use Bitrix\Sale\Order;

class MessageNotifyHandler
{
    private $cashbackAmount;

    private $currency;

    private $orderId;

    private $userId;

    private $cashbackAmountFormatted;

    /** @var array */
    private $userData = [];

    /** @var Order */
    private $order;

    /** @var string */
    private $siteName;

    private $siteId;

    public function __construct(float $cashbackAmount, string $currency, int $orderId, int $userId)
    {
        $this->cashbackAmount = $cashbackAmount;
        $this->currency = $currency;
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->cashbackAmountFormatted = FormatCurrency($this->cashbackAmount, $this->currency);

        $this->fillUserData();
        $this->fillCurrentSite();
    }

    public function sendEmail()
    {
        if (empty($this->userData['EMAIL'])) {
            return;
        }

        Event::send(array(
            "EVENT_NAME" => 'LOGICASOFT_CASHBACK_ADD_EMAIL',
            "LID" => $this->siteId,
            "C_FIELDS" => array(
                "EMAIL_TO" => $this->userData['EMAIL'],
                "ORDER_ID" => $this->orderId,
                'SUM' => $this->cashbackAmountFormatted,
                'CURRENCY' => $this->currency,
                'SITE_NAME' => $this->siteName
            ),
        ));
    }

    public function sendSms()
    {
        if (!\Bitrix\Main\Loader::includeModule('messageservice')) {
            return false;
        }

        if (empty($this->userData['PHONE_NUMBER'])) {
            return false;
        }

        $senderId = Option::get('main', 'sms_default_service', null);

        if (is_null($senderId)) {
            return false;
        }

        $sender = SmsManager::getSenderById($senderId);

        $sms = new \Bitrix\Main\Sms\Event(
            'LOGICASOFT_CASHBACK_ADD_SMS',
            [
                'USER_PHONE' => $this->userData['PHONE_NUMBER'],
                "ORDER_ID" => $this->orderId,
                'SUM' => $this->cashbackAmountFormatted,
                'CURRENCY' => $this->currency,
                'SITE_NAME' => $this->siteName,
                'DEFAULT_SENDER' => $sender->getDefaultFrom(),
            ]
        );
        $sms->setSite($this->siteId);
        $result = $sms->send(false);

        if (!$result->isSuccess()) {
            throw new \Exception(implode(', ', $result->getErrorMessages()));
        }
    }

    protected function fillUserData()
    {
        $this->order = Order::load($this->orderId);
        $propertyCollection = $this->order->getPropertyCollection();

        if (null !== ($propertyUserEmail = $propertyCollection->getUserEmail())) {
            $this->userData['EMAIL'] = $propertyUserEmail->getValue();
        }

        if (null !== ($propertyPhone = $propertyCollection->getPhone())) {
            $this->userData['PHONE_NUMBER'] = $propertyPhone->getValue();
        }
    }

    protected function fillCurrentSite()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $host = $request->getHttpHost();

        $site = SiteTable::query()
            ->setSelect(['LID', 'SITE_NAME'])
            ->where(
                Query::filter()
                    ->logic('or')
                    ->where('SERVER_NAME', $host)
                    ->where('DEF', 'Y')
            )
            ->exec()
            ->fetch();

        if (!empty($site['LID'])) {
            $this->siteId = $site['LID'];
            $this->siteName = $site['SITE_NAME'];
        }
    }
}