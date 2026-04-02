<?php

declare(strict_types=1);

namespace Postroyka\AppBundle\Service\Cart\Discount\Handler;

use Postroyka\AppBundle\Entity\Cart\CartItem;
use Postroyka\AppBundle\Service\Cart\Discount\AbstractDiscountHandler;
use Postroyka\AppBundle\Service\Cart\Discount\DiscountContext;
use Postroyka\AppBundle\Service\Cart\Discount\DiscountResult;
use Submarine\PagesBundle\Entity\Page;

/**
 * Применяет процентную скидку, если клиент достиг порога оптового количества.
 */
final class WholesaleDiscountHandler extends AbstractDiscountHandler
{
    private const PRIORITY = 100;
    private const WHOLESALE_QUANTITY_FIELD = 'wholesale_quantity';
    private const WHOLESALE_DISCOUNT_FIELD = 'wholesale_discount';

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    protected function isApplicable(CartItem $item, DiscountContext $context): bool
    {
        $product = $item->getProduct();

        if ($product->getValue(Page::STOCK_VALUE) || $item->getIsDefected()) {
            return false;
        }

        if (!$product->getValue(self::WHOLESALE_QUANTITY_FIELD) || !$product->getValue(self::WHOLESALE_DISCOUNT_FIELD)) {
            return false;
        }

        return $item->getFullProductQuantity($context->getCart()) >= (float) $product->getValue(self::WHOLESALE_QUANTITY_FIELD);
    }

    protected function calculateDiscount(CartItem $item, DiscountContext $context): DiscountResult
    {
        $discountPercent = (float) $item->getProduct()->getValue(self::WHOLESALE_DISCOUNT_FIELD);

        return DiscountResult::percentage($discountPercent);
    }
}
