<?php

declare(strict_types=1);

namespace Postroyka\AppBundle\Service\Cart\Discount;

use Postroyka\AppBundle\Entity\Cart\CartItem;

/**
 * Описывает один шаг в цепочке расчета скидки.
 */
interface DiscountHandlerInterface
{
    public function setNext(DiscountHandlerInterface $handler): DiscountHandlerInterface;

    public function handle(CartItem $item, DiscountContext $context): float;

    public function getPriority(): int;
}
