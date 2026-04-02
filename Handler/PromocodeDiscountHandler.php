<?php

declare(strict_types=1);

namespace Postroyka\AppBundle\Service\Cart\Discount\Handler;

use Postroyka\AppBundle\Entity\Cart\CartItem;
use Postroyka\AppBundle\Entity\Promocode;
use Postroyka\AppBundle\Service\Cart\Discount\AbstractDiscountHandler;
use Postroyka\AppBundle\Service\Cart\Discount\DiscountContext;
use Postroyka\AppBundle\Service\Cart\Discount\DiscountResult;

/**
 * Применяет процентную или пропорционально распределенную фиксированную скидку по активному промокоду.
 */
final class PromocodeDiscountHandler extends AbstractDiscountHandler
{
    private const PRIORITY = 400;
    private const EXCLUDED_PROMOCODE_TYPES = [
        Promocode::FREE_DELIVERY_TYPE,
        Promocode::FREE_UNLOADING_TYPE,
        Promocode::FREE_DELIVERY_AND_UNLOADING_TYPE,
    ];

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    protected function isApplicable(CartItem $item, DiscountContext $context): bool
    {
        $cart = $context->getCart();
        $promocode = $cart->getPromocode();

        if ($promocode === null) {
            return false;
        }

        if (!$cart->isAllowPromocode() || !$cart->isAllowAnyDiscount($item)) {
            return false;
        }

        if ($this->isProductDiscountExcluded($promocode)) {
            return false;
        }

        return $promocode->getDiscountPercent() > 0 || $promocode->getDiscountFixed() > 0;
    }

    protected function calculateDiscount(CartItem $item, DiscountContext $context): DiscountResult
    {
        $promocode = $context->getCart()->getPromocode();

        if ($promocode->getDiscountPercent() > 0) {
            return DiscountResult::percentage((float) $promocode->getDiscountPercent());
        }

        if ($promocode->getDiscountFixed() > 0) {
            return DiscountResult::fixedAmount(
                $this->calculateProportionalFixedDiscount($item, $context, (float) $promocode->getDiscountFixed())
            );
        }

        return DiscountResult::empty();
    }

    private function calculateProportionalFixedDiscount(
        CartItem $item,
        DiscountContext $context,
        float $fixedDiscount
    ): float {
        $cartTotal = $context->getCartItemsPrice();
        $itemLength = $item->getFullLength();

        if ($cartTotal <= 0 || $itemLength <= 0) {
            return 0.0;
        }

        $itemTotalDiscount = $fixedDiscount * ($item->getTotalPrice() / $cartTotal);

        return $itemTotalDiscount / $itemLength;
    }

    private function isProductDiscountExcluded(Promocode $promocode): bool
    {
        return in_array($promocode->getType(), self::EXCLUDED_PROMOCODE_TYPES, true);
    }
}
