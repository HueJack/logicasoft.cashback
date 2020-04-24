<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/
$MESS['LOGICASOFT_CASHBACK_MODULE_NAME'] = 'Кэшбек с покупок';
$MESS['LOGICASOFT_CASHBACK_MODULE_DESC'] = 'Возвращает % с покупок на внутренний счет клиента';
$MESS['LOGICASOFT_CASHBACK_PARTNER_NAME'] = 'LLC Logicasoft';
$MESS['LOGICASOFT_CASHBACK_UNINSTALL_STEP_1_TITLE'] = 'Подтвердите удаление данных';
$MESS['LOGICASOFT_CASHBACK_TYPE_EVENT_NAME'] = 'Начисления кэшбека после выполнения заказа';
$MESS['LOGICASOFT_CASHBACK_TYPE_EMAIL_EVENT_DESCRIPTION'] = '
                #SUM# - Сумма кэшбека
                #EMAIL_TO# - Email клиента(из заказа),
                #SITE_NAME# - Название сайта,
                #ORDER_ID# - Номер заказа
                ';
$MESS['LOGICASOFT_CASHBACK_TYPE_SMS_EVENT_DESCRIPTION'] = '
                #SUM# - Сумма кэшбека
                #USER_PHONE# - Номер телефона клиента(из заказа),
                #SITE_NAME# - Название сайта,
                #ORDER_ID# - Номер заказа
                ';
$MESS['LOGICASOFT_CASHBACK_TYPE_EMAIL_EVENT_MESSAGE_SUBJECT'] = 'Начисление кэшбека по заказу №#ODER_ID#';
$MESS['LOGICASOFT_CASHBACK_TYPE_EMAIL_EVENT_MESSAGE_MESSAGE'] = '
                Информационное сообщение сайта #SITE_NAME#
                
                За заказ №#ORDER_ID# Вам начислен кэшбек в сумме #SUM#. 
                
                #SITE_NAME# 
                ';
$MESS['LOGICASOFT_CASHBACK_TYPE_SMS_EVENT_MESSAGE'] = 'Вам начислен кэшбек в размере #SUM#';
