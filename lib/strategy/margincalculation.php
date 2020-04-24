<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace Logicasoft\Cashback\Strategy;

/**
 * Расчет от маржинальности товара(розничная - закупочная(если есть))
 *
 * Class MarginCalculation
 * @package Logicasoft\Cashback
 */
class MarginCalculation implements StrategyInterface
{
    public static function getTitle(): string
    {
        return 'Расчет от маржинальности товара';
    }

    public static function calculate(array $product, float $percent)
    {
        self::checkFields($product);

        $fullPrice = ((float)$product['PRICE'] - (float)$product['CATALOG_PURCHASING_PRICE']) * $product['QUANTITY'];

        return $fullPrice * ($percent / 100);
    }

    private static function checkFields(array $product)
    {
        $fields = [
            'CATALOG_PURCHASING_PRICE',
            'CATALOG_PURCHASING_CURRENCY',
            'CURRENCY'
        ];

        foreach ($fields as $fieldCode) {
            if (!array_key_exists($fieldCode, $product)) {
                throw new \InvalidArgumentException(
                    'Ошибка! В массиве продукта отсутствуют нужные поля ' .
                    print_r(['NEED_FIELDS' => $fields, 'PRODUCT' => $product], true)
                );
            }
        }
    }
}