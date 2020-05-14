<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

use Bitrix\Main\Localization\Loc;

$moduleId = 'logicasoft.cashback';

\Bitrix\Main\Loader::includeModule($moduleId);

//IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$moduleId.'/options.php');

$showRightsTab = true;

$strategies = \Logicasoft\Cashback\Strategy\Manager::getList();
$strategiesSelectDataset = [
    'REFERENCE_ID' => array_keys($strategies),
    'REFERENCE' => array_values($strategies)
];

$arTabs = array(
    array(
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('LLC_CASHBACK_OPTIONS_TAB_CAPTION'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('LLC_CASHBACK_OPTIONS_TAB_CAPTION')
    )
);

$arGroups = array(
    'MAIN' => array('TITLE' => Loc::getMessage('LLC_CASHBACK_OPTIONS_TAB_CAPTION'), 'TAB' => 0)
);

$arOptions = array(
    'PERCENT_CASHBACK' => array(
        'GROUP' => 'MAIN',
        'TITLE' => Loc::getMessage('LLC_CASHBACK_OPTIONS_PERSENT_OPTION'),
        'TYPE' => 'INT',
        'DEFAULT' => '0',
        'SORT' => '0',
        'STEP' => 'any',
        'VALIDATOR' => function($value) {
            if (!is_numeric($value)) {
                return new \Bitrix\Main\Error('Значение поля "Размер кэшбека в %" может быть только числом.');
            }

            if ($value < 0) {
                return new \Bitrix\Main\Error('Значение поля "Размер кэшбека в %" не может быть отрицательным');
            }

            return true;
        }
    ),
    'DONT_CASHBACK_FROM_PRODUCTS_WITH_DISCOUNT' => array(
        'GROUP' => 'MAIN',
        'TITLE' => Loc::getMessage('LLC_CASHBACK_OPTIONS_DONT_CASHBACK_FROM_PRODUCTS_WITH_DISCOUNT'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '0',
        'SORT' => '1',
    ),
    'CALCULATE_STRATEGY' => array(
        'GROUP' => 'MAIN',
        'TITLE' => Loc::getMessage('LLC_CASHBACK_OPTIONS_STRATEGY_CALCULATION'),
        'TYPE' => 'SELECT',
        'VALUES' => $strategiesSelectDataset,
        'SORT' => '2',
    ),
    'SEND_EMAIL_NOTIFICATION_ABOUT_ADDED_CASHBACK' => array(
        'GROUP' => 'MAIN',
        'TITLE' => Loc::getMessage('LLC_CASHBACK_OPTIONS_SEND_EMAIL_NOTIFICATION_ABOUT_ADDED_CASHBACK'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '1',
        'SORT' => '3',
    ),
    'SEND_SMS_NOTIFICATION_ABOUT_ADDED_CASHBACK' => array(
        'GROUP' => 'MAIN',
        'TITLE' => Loc::getMessage('LLC_CASHBACK_OPTIONS_SEND_SMS_NOTIFICATION_ABOUT_ADDED_CASHBACK'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '1',
        'SORT' => '4',
    ),
);

/*
Конструктор класса CModuleOptions
$module_id - ID модуля
$arTabs - массив вкладок с параметрами
$arGroups - массив групп параметров
$arOptions - собственно сам массив, содержащий параметры
$showRightsTab - определяет надо ли показывать вкладку с настройками прав доступа к модулю ( true / false )
*/

$opt = new \Logicasoft\Cashback\CModuleOptions($moduleId, $arTabs, $arGroups, $arOptions, $showRightsTab);
$opt->ShowHTML();
