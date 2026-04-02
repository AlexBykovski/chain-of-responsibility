<?php

declare(strict_types=1);

namespace Postroyka\AppBundle\Service\Cart\Discount\Handler;

use Postroyka\AppBundle\Entity\Cart\CartItem;
use Postroyka\AppBundle\Service\Cart\Discount\AbstractDiscountHandler;
use Postroyka\AppBundle\Service\Cart\Discount\DiscountContext;
use Postroyka\AppBundle\Service\Cart\Discount\DiscountResult;
use Postroyka\AppBundle\Service\OrderCalculator;

/**
 * Применяет процент скидки по карте пользователя, если скидки по карте разрешены.
 */
final class DiscountCardHandler extends AbstractDiscountHandler
{
    private const PRIORITY = 200;
    private const DISABLE_DISCOUNT_FIELD = 'disable_discount';

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    protected function isApplicable(CartItem $item, DiscountContext $context): bool
    {
        $user = $context->getUser();

        if (!$context->getCart()->isAllowAnyDiscount($item)) {
            return false;
        }

        if (!$context->canApplyDiscountCard()) {
            return false;
        }

        return $user !== null && (bool) $user->getId() && (float) $user->getDiscount() > 0;
    }

    protected function calculateDiscount(CartItem $item, DiscountContext $context): DiscountResult
    {
        $user = $context->getUser();
        $product = $item->getProduct();

        if ($product->getValue(self::DISABLE_DISCOUNT_FIELD)) {
            return DiscountResult::percentage((float) OrderCalculator::FIXED_DISCOUNT);
        }

        return DiscountResult::percentage((float) $user->getDiscount());
    }
}
