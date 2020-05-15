<?php
/**
 * Created by: Nikolay Mesherinov
 * Email: mnikolayw@gmail.com
 **/

namespace Logicasoft\Cashback;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Logicasoft\Cashback\Strategy\StrategyInterface;

/**
 * Используя выбранную стратегию подсчитает размер кэшбека
 *
 * Class CalculateHandler
 * @package Logicasoft\Cashback
 */
class CalculateHandler
{
    /** @var Order */
    private $order;

    /** @var StrategyInterface */
    private $strategy;

    /** @var array */
    private $basketProducts;

    /** @var float */
    private $cashbackValue = 0.0;

    /** @var float */
    private $percentCashback;

    public function __construct(Order $order, StrategyInterface $strategy)
    {
        $this->order = $order;
        $this->strategy = $strategy;
        $this->percentCashback = \COption::GetOptionString('logicasoft.cashback', 'PERCENT_CASHBACK', 0);

        $this->fillProducts();
        $this->calculate();
    }

    /**
     * Возвращает величину кэшбека
     *
     * @return float
     */
    public function getCashbackValue(): float
    {
        return (float)$this->cashbackValue;
    }

    private function calculate()
    {
        if (0 == sizeof($this->basketProducts)) {
            return;
        }

        foreach ($this->basketProducts as $product) {
            $this->cashbackValue += $this->strategy::calculate($product, (float)$this->percentCashback);
        }
    }

    /**
     * Заполнит свойство объекта продуктами из корзины заказа
     */
    private function fillProducts()
    {
        $dontCashbackFromProductsWithDiscount = \COption::GetOptionString(
            'logicasoft.cashback',
            'DONT_CASHBACK_FROM_PRODUCTS_WITH_DISCOUNT',
            'N'
        );

        /** @var BasketItem $basketItem */
        foreach ($this->order->getBasket()->getBasketItems() as $basketItem) {
            if ($dontCashbackFromProductsWithDiscount == 'Y' && $basketItem->getDiscountPrice() > 0) {
                continue;
            }

            $this->basketProducts[$basketItem->getProductId()] = [
                'PRICE' => $basketItem->getPrice(),
                'QUANTITY' => $basketItem->getQuantity(),
                'CURRENCY' => $basketItem->getCurrency()
            ];
        }

        if (0 == sizeof($this->basketProducts)) {
            return;
        }

        $productIterator = \CIBlockElement::GetList(
            [],
            [
                'ID' => array_keys($this->basketProducts),
            ],
            false,
            false,
            [
                'ID',
                'IBLOCK_ID',
                'CATALOG_PURCHASING_PRICE'
            ]
        );

        while ($item = $productIterator->Fetch()) {
            $this->basketProducts[$item['ID']] = array_merge($this->basketProducts[$item['ID']], $item);
        }

        $event = new Event(
            'logicasoft.cashback',
            'onAfterFillProducts',
            [
                'orderId' => $this->order->getId(),
                'basketProducts' => $this->basketProducts
            ]
        );
        $event->send();
        $results = $event->getResults();

        /** @var \Bitrix\Main\Entity\EventResult $result */
        foreach ($results as $result) {
            if (!($result instanceof EventResult)) {
                continue;
            }

            if ($result->getType() == EventResult::SUCCESS) {
                $modifiedProducts = $result->getModified();
                if (is_array($modifiedProducts) && sizeof($modifiedProducts)) {
                    foreach ($modifiedProducts as $id => $item) {
                        if (array_key_exists($id, $this->basketProducts)) {
                            $this->basketProducts[$id] = array_merge($this->basketProducts[$id], $item);
                        } else {
                            $this->basketProducts[$id] = $item;
                        }
                    }
                }

                $productsToDelete = $result->getUnset();
                if (is_array($productsToDelete) && sizeof($productsToDelete)) {
                    foreach ($productsToDelete as $id) {
                        if (!array_key_exists($id, $this->basketProducts)) {
                            continue;
                        }

                        unset($this->basketProducts[$id]);
                    }
                }
            }
        }
    }
}