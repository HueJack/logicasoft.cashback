<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace Logicasoft\Cashback\Strategy;

class RetailCalculation implements StrategyInterface
{
    public static function getTitle(): string
    {
        return 'Расчет от розничной';
    }

    public static function calculate(array $product, float $percent)
    {
        self::checkFields($product);

        $fullPrice = (float)$product['PRICE'] * (int)$product['QUANTITY'];

        return $fullPrice * ($percent / 100);
    }

    private static function checkFields(array $product)
    {
        $fields = [
            'PRICE',
            'QUANTITY'
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