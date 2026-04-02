<?php

declare(strict_types=1);

namespace Postroyka\AppBundle\Service\Cart\Discount;

use Postroyka\AppBundle\Entity\Cart\CartItem;

/**
 * Реализует общий алгоритм цепочки и оставляет наследникам только логику конкретного правила.
 */
abstract class AbstractDiscountHandler implements DiscountHandlerInterface
{
    private ?DiscountHandlerInterface $nextHandler = null;

    final public function setNext(DiscountHandlerInterface $handler): DiscountHandlerInterface
    {
        $this->nextHandler = $handler;

        return $handler;
    }

    final public function handle(CartItem $item, DiscountContext $context): float
    {
        $this->applyCurrentDiscount($item, $context);

        if ($this->nextHandler !== null) {
            return $this->nextHandler->handle($item, $context);
        }

        return $this->calculateFinalDiscount($item, $context);
    }

    abstract protected function isApplicable(CartItem $item, DiscountContext $context): bool;

    abstract protected function calculateDiscount(CartItem $item, DiscountContext $context): DiscountResult;

    private function applyCurrentDiscount(CartItem $item, DiscountContext $context): void
    {
        if (!$this->isApplicable($item, $context)) {
            return;
        }

        $context->addDiscountResult($this->calculateDiscount($item, $context));
    }

    private function calculateFinalDiscount(CartItem $item, DiscountContext $context): float
    {
        $basePrice = $this->resolveBasePrice($item);
        $percentDiscount = $basePrice * $context->getTotalDiscountPercent() / 100;
        $fixedDiscount = $context->getTotalDiscountAmount();
        $totalDiscount = min($basePrice, $percentDiscount + $fixedDiscount);

        return round($totalDiscount, 2);
    }

    private function resolveBasePrice(CartItem $item): float
    {
        $product = $item->getProduct();

        if ($item->getIsDefected()) {
            return (float) $product->getPageDefected()->getCost();
        }

        return (float) $product->getPrice();
    }
}
