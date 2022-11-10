<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartItems;

/**
 * @todo Implement this class
 */
class AddPaymentInfo implements EventInterface
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
        $paymentMethod = $this->cart->getPayment()->getMethod();
        $currencyCode = $this->cart->getQuoteCurrencyCode();
        return [
            'event' => 'add_payment_info',
            'ecommerce' => [
                'currency' => $currencyCode,
                'value' => $this->cart->getGrandTotal(),
                'coupon' => $this->cart->getCouponCode(),
                'payment_type' => $paymentMethod,
                'items' => $this->cartItems->get()
            ]
        ];
    }
}
