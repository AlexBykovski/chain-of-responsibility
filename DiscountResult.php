<?php

declare(strict_types=1);

namespace Postroyka\AppBundle\Service\Cart\Discount;

use InvalidArgumentException;

/**
 * Неизменяемый value object, который хранит числовой результат одного правила скидки.
 */
final class DiscountResult
{
    private function __construct(
        private readonly float $discountPercent,
        private readonly float $discountAmount
    ) {
        if ($this->discountPercent < 0) {
            throw new InvalidArgumentException('Discount percent must be greater than or equal to zero.');
        }

        if ($this->discountAmount < 0) {
            throw new InvalidArgumentException('Discount amount must be greater than or equal to zero.');
        }

        if ($this->discountPercent > 0 && $this->discountAmount > 0) {
            throw new InvalidArgumentException('A discount result can contain either percent or fixed amount, not both.');
        }
    }

    public static function percentage(float $discountPercent): self
    {
        return new self($discountPercent, 0.0);
    }

    public static function fixedAmount(float $discountAmount): self
    {
        return new self(0.0, $discountAmount);
    }

    public static function empty(): self
    {
        return new self(0.0, 0.0);
    }

    public function getDiscountPercent(): float
    {
        return $this->discountPercent;
    }

    public function getDiscountAmount(): float
    {
        return $this->discountAmount;
    }

    public function isEmpty(): bool
    {
        return $this->discountPercent === 0.0 && $this->discountAmount === 0.0;
    }
}

