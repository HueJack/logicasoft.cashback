<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace Logicasoft\Cashback\Strategy;

use Bitrix\Main\Localization\Loc;

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
        return Loc::getMessage('LLC_CASHBACK_MARGINCALCULATION_TITLE');
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
                    Loc::getMessage('LLC_CASHBACK_MARGINCALCULATION_CHECK_FIELD_ERROR') .
                    print_r(['NEED_FIELDS' => $fields, 'PRODUCT' => $product], true)
                );
            }
        }
    }
}