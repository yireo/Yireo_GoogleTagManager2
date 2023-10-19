<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Event;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote as Cart;
use AdPage\GTM\Api\Data\EventInterface;
use AdPage\GTM\DataLayer\Tag\Cart\CartItems;

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
        
        if (empty($shippingMethod) && $this->checkoutSession->hasQuote()) {
            $quoteId = $this->checkoutSession->getQuote()->getId();
            $shippingMethod = $this->getShippingMethodFromQuote((int)$quoteId);
        }

        if (empty($shippingMethod)) {
            return [];
        }

        return [
            'event' => 'trytagging_add_shipping_info',
            'ecommerce' => [
                'shipping_tier' => $shippingMethod,
                'items' => $this->cartItems->get(),
            ],
        ];
    }

    /**
     * @param int $quoteId
     * @return string|null
     */
    public function getShippingMethodFromQuote(int $quoteId): ?string
    {
        $shippingMethod = $this->shippingMethodManagement->get($quoteId);
        if (empty($shippingMethod)) {
            return null;
        }

        return $shippingMethod->getCarrierCode().'_'.$shippingMethod->getMethodCode();
    }
}
