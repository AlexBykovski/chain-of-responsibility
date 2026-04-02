<?php

declare(strict_types=1);

namespace Postroyka\AppBundle\Service\Cart\Discount;

use Postroyka\AccountBundle\Entity\ExtendedUser;
use Postroyka\AppBundle\Entity\Cart\Cart;

/**
 * Передает общий контекст корзины и накапливает результаты скидок при расчете одного товара.
 */
final class DiscountContext
{
    /** @var DiscountResult[] */
    private array $appliedDiscounts = [];

    private readonly bool $discountCardAllowed;
    private readonly bool $orderAmountDiscountAllowed;

    public function __construct(
        private Cart $cart,
        private ?ExtendedUser $user
    ) {
        $this->discountCardAllowed = $cart->isAllowDiscountCard();
        $this->orderAmountDiscountAllowed = $cart->isAllowDiscountBySum();
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getUser(): ?ExtendedUser
    {
        return $this->user;
    }

    public function canApplyDiscountCard(): bool
    {
        return $this->discountCardAllowed;
    }

    public function canApplyOrderAmountDiscount(): bool
    {
        return $this->orderAmountDiscountAllowed;
    }

    public function addDiscountResult(DiscountResult $result): void
    {
        if ($result->isEmpty()) {
            return;
        }

        $this->appliedDiscounts[] = $result;
    }

    public function clearAppliedDiscounts(): void
    {
        $this->appliedDiscounts = [];
    }

    public function getTotalDiscountPercent(): float
    {
        $totalPercent = 0.0;

        foreach ($this->appliedDiscounts as $discount) {
            $totalPercent += $discount->getDiscountPercent();
        }

        return $totalPercent;
    }

    public function getTotalDiscountAmount(): float
    {
        $totalAmount = 0.0;

        foreach ($this->appliedDiscounts as $discount) {
            $totalAmount += $discount->getDiscountAmount();
        }

        return $totalAmount;
    }

    public function getCartItemsPrice(): float
    {
        return (float) $this->cart->getItemsPrice();
    }
}
