<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartItems;

class AddShippingInfo implements EventInterface
{
    private CartInterface $cart;
    private CartItems $cartItems;

    /**
     * @param Quote $cart
     * @param CartItems $cartItems
     */
    public function __construct(
        Quote $cart,
        CartItems $cartItems
    ) {
        $this->cart = $cart;
        $this->cartItems = $cartItems;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        $shippingMethod = $this->cart->getShippingAddress()->getShippingMethod();

        return [
            'event' => 'add_shipping_info',
            'ecommerce' => [
                'shipping_tier' => $shippingMethod,
                'items' => $this->cartItems->get()
            ]
        ];
    }
}
