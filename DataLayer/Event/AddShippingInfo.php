<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote as Cart;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartItems;

class AddShippingInfo implements EventInterface
{
    private Cart $cart;
    private CartItems $cartItems;
    private ShippingMethodManagementInterface $shippingMethodManagement;
    private CheckoutSession $checkoutSession;

    /**
     * @param Cart $cart
     * @param CartItems $cartItems
     */
    public function __construct(
        Cart $cart,
        CartItems $cartItems,
        ShippingMethodManagementInterface $shippingMethodManagement,
        CheckoutSession $checkoutSession
    ) {
        $this->cart = $cart;
        $this->cartItems = $cartItems;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        $shippingMethod = $this->cart->getShippingAddress()->getShippingMethod();

        if (!$shippingMethod) {
            $quoteId = $this->checkoutSession->getQuote()->getId();
            $shippingMethod = $this->shippingMethodManagement->get($quoteId);
            $shippingMethod = $shippingMethod->getCarrierCode().'_'.$shippingMethod->getMethodCode();
        }

        if (!$shippingMethod) {
            return [];
        }

        return [
            'event' => 'add_shipping_info',
            'ecommerce' => [
                'shipping_tier' => $shippingMethod,
                'items' => $this->cartItems->get(),
            ],
        ];
    }
}
