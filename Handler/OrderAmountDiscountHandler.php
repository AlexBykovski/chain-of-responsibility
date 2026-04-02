<?php

declare(strict_types=1);

namespace Postroyka\AppBundle\Service\Cart\Discount\Handler;

use Postroyka\AppBundle\Entity\Cart\CartItem;
use Postroyka\AppBundle\Service\Cart\Discount\AbstractDiscountHandler;
use Postroyka\AppBundle\Service\Cart\Discount\DiscountContext;
use Postroyka\AppBundle\Service\Cart\Discount\DiscountResult;
use Postroyka\AppBundle\Service\OrderCalculator;

/**
 * Применяет процентную скидку на заказ, если корзина проходит правило по сумме.
 */
final class OrderAmountDiscountHandler extends AbstractDiscountHandler
{
    private const PRIORITY = 300;
    private const DISCOUNT_PERCENT = 2.0;

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    protected function isApplicable(CartItem $item, DiscountContext $context): bool
    {
        $cart = $context->getCart();

        if (!$cart->isAllowAnyDiscount($item)) {
            return false;
        }

        if (!$context->canApplyOrderAmountDiscount()) {
            return false;
        }

        return $cart->getPaymentMethod() !== OrderCalculator::LEGAL_PERSON_TRANSLATE;
    }

    protected function calculateDiscount(CartItem $item, DiscountContext $context): DiscountResult
    {
        return DiscountResult::percentage(self::DISCOUNT_PERCENT);
    }
}
