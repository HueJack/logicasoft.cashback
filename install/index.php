<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Sms\TemplateTable;
use Logicasoft\Cashback\EventsHandler;

IncludeModuleLangFile(__FILE__);

class logicasoft_cashback extends CModule
{
    const MODULE_ID = 'logicasoft.cashback';
    public $MODULE_ID = 'logicasoft.cashback';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public static $EMAIL_EVENT_TYPE = 'LOGICASOFT_CASHBACK_ADD_EMAIL';
    public static $SMS_EVENT_TYPE = 'LOGICASOFT_CASHBACK_ADD_SMS';

    public function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("LOGICASOFT_CASHBACK_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("LOGICASOFT_CASHBACK_MODULE_DESC");
        $this->PARTNER_NAME = GetMessage("LOGICASOFT_CASHBACK_PARTNER_NAME");
        $this->PARTNER_URI = "https://logicasoft.pro";
    }

    public function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler(
            'sale',
            'OnSaleStatusOrder',
            static::MODULE_ID,
            EventsHandler::class,
            'calculateCashback'
        );

        EventManager::getInstance()->registerEventHandler(
            static::MODULE_ID,
            'onAfterCashbackAdd',
            static::MODULE_ID,
            EventsHandler::class,
            'sendNotifyMessages'
        );
    }

    public function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'sale',
            'OnSaleStatusOrder',
            static::MODULE_ID,
            EventsHandler::class,
            'calculateCashback'
        );

        EventManager::getInstance()->unRegisterEventHandler(
            static::MODULE_ID,
            'onAfterCashbackAdd',
            static::MODULE_ID,
            EventsHandler::class,
            'sendNotifyMessages'
        );
    }

    public static function InstallMailEvents()
    {
        $site = SiteTable::query()
            ->setFilter(['DEF' => 'Y'])
            ->exec()
            ->fetchObject();

        CEventType::Add([
            'EVENT_NAME'    => self::$EMAIL_EVENT_TYPE,
            'NAME'          => GetMessage('LOGICASOFT_CASHBACK_TYPE_EVENT_TITLE'),
            'LID'           => SITE_ID,
            'EVENT_TYPE' => 'email',
            'DESCRIPTION'   => GetMessage('LOGICASOFT_CASHBACK_TYPE_EMAIL_EVENT_DESCRIPTION')
        ]);

        CEventType::Add([
            'EVENT_NAME'    => self::$SMS_EVENT_TYPE,
            'NAME'          => GetMessage('LOGICASOFT_CASHBACK_TYPE_EVENT_TITLE'),
            'LID'           => SITE_ID,
            'EVENT_TYPE' => 'sms',
            'DESCRIPTION'   => GetMessage('LOGICASOFT_CASHBACK_TYPE_SMS_EVENT_DESCRIPTION')
        ]);

        $eventMessages = self::getEventMessages();
        if (0 == sizeof($eventMessages)) {
            $eventMessage = new CEventMessage();
            $eventMessage->Add([
                'ACTIVE' => 'Y',
                'EVENT_NAME' => self::$EMAIL_EVENT_TYPE,
                'LID' => [$site->getLid()],
                'EMAIL_FROM' => '#SALE_EMAIL#',
                'EMAIL_TO' => '#EMAIL_TO#',
                'BCC' => '',
                'SUBJECT' => GetMessage('LOGICASOFT_CASHBACK_TYPE_EMAIL_EVENT_MESSAGE_SUBJECT'),
                'BODY_TYPE' => 'text',
                'MESSAGE' => GetMessage('LOGICASOFT_CASHBACK_TYPE_EMAIL_EVENT_MESSAGE_MESSAGE')
            ]);
        }

        if (Loader::includeModule('messageservice')) {
            $smsTemplates = static::getSmsTemplates();

            if (0 == sizeof($smsTemplates)) {
                $result = TemplateTable::add([
                    'EVENT_NAME' => self::$SMS_EVENT_TYPE,
                    'SENDER' => '#DEFAULT_SENDER#',
                    'RECEIVER' => '#USER_PHONE#',
                    'MESSAGE' => GetMessage('LOGICASOFT_CASHBACK_TYPE_SMS_EVENT_MESSAGE'),
                ]);

                $template = TemplateTable::getEntity()->wakeUpObject($result->getId());
                $template->addToSites($site);

                $template->save();
            }
        }
    }

    public static function UnInstallMailEvents()
    {
        if (empty($_REQUEST['savedata'])) {
            CEventType::Delete(['EVENT_NAME' => self::$EMAIL_EVENT_TYPE]);
            CEventType::Delete(['EVENT_NAME' => self::$SMS_EVENT_TYPE]);

            $eventMessages = self::getEventMessages();
            foreach ($eventMessages as $eventMessage) {
                CEventMessage::Delete($eventMessage['ID']);
            }

            if (Loader::includeModule('messageservice')) {
                $smsTemplates = static::getSmsTemplates();

                foreach ($smsTemplates as $smsTemplate) {
                    TemplateTable::delete($smsTemplate['ID']);
                }
            }
        }
    }

    public function DoInstall()
    {
        ModuleManager::registerModule(static::MODULE_ID);

        self::InstallEvents();
        self::InstallMailEvents();
    }

    public function DoUninstall()
    {
        global $APPLICATION, $step;
        if ($step < 1) {
            $APPLICATION->IncludeAdminFile(
                GetMessage('LOGICASOFT_CASHBACK_UNINSTALL_STEP_1_TITLE'),
                dirname(__FILE__) . '/unstep1.php'
            );
        }
        if ($step == 2) {
            self::UnInstallEvents();
            self::UnInstallMailEvents();
        }

        ModuleManager::unRegisterModule(static::MODULE_ID);
    }

    protected static function getEventMessages(): array
    {
        $result = [];

        $by = 'SORT';
        $order = 'ASC';
        $messagesIterator = CEventMessage::GetList($by, $order, ['EVENT_NAME' => self::$EMAIL_EVENT_TYPE]);
        while ($item = $messagesIterator->Fetch()) {
            $result[] = $item;
        }

        return $result;
    }

    protected static function getSmsTemplates(): array
    {
        $result = [];

        if (!Loader::includeModule('messageservice')) {
            return $result;
        }

        $templateIterator = TemplateTable::query()
            ->setSelect(['ID'])
            ->setFilter(['EVENT_NAME' => self::$SMS_EVENT_TYPE])
            ->exec();
        while ($item = $templateIterator->fetch()) {
            $result[] = $item;
        }

        return $result;
    }
}


