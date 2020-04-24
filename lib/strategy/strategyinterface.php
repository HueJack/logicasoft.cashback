<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace Logicasoft\Cashback\Strategy;

interface StrategyInterface
{
    public static function getTitle(): string;

    /**
     * Вернет сумму кэшбека для продукта.
     *
     * Ожидается, что в $product есть ключи [
     * 'PRICE' => 0, Цена продукта в корзине с учетом скидок
     * 'QUANTITY' => 0, Количество продуктов в корзине
     * 'CURRENCY' => 'RUB', Валюта заказа
     * 'CATALOG_PURCHASING_PRICE' => 0, Цена закупки
     * 'CATALOG_PURCHASING_CURRENCY' => RUB Валюта закупки
     * ]
     *
     * @param array $product
     * @param float $percent
     * @return mixed
     */
    public static function calculate(array $product, float $percent);
}