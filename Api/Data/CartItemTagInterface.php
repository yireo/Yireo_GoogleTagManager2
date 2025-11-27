<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Api\Data;

use Magento\Quote\Model\Quote\Item as CartItem;

interface CartItemTagInterface extends TagInterface
{
    /**
     * @return mixed
     */
    public function setCartItem(CartItem $cartItem);
}
