<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartItems;

class AddPaymentInfo implements EventInterface
{
    private CartItems $cartItems;
    private CartRepositoryInterface $cartRepository;
    private int $cartId;
    private string $paymentMethod;

    /**
     * @param Quote $cart
     * @param CartRepositoryInterface $cartRepository
     * @param CartItems $cartItems
     */
    public function __construct(
        CartRepositoryInterface  $cartRepository,
        CartItems $cartItems
    ) {
        $this->cartItems = $cartItems;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        $cart = $this->cartRepository->get($this->cartId);
        return [
            'event' => 'add_payment_info',
            'ecommerce' => [
                'currency' => $cart->getQuoteCurrencyCode(),
                'value' => $cart->getGrandTotal(),
                'coupon' => $cart->getCouponCode(),
                'payment_type' => $this->paymentMethod,
                'items' => $this->cartItems->get()
            ]
        ];
    }

    /**
     * @param string $paymentMethod
     * @return AddPaymentInfo
     */
    public function setPaymentMethod(string $paymentMethod): AddPaymentInfo
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @param int $cartId
     * @return AddPaymentInfo
     */
    public function setCartId(int $cartId): AddPaymentInfo
    {
        $this->cartId = $cartId;
        return $this;
    }
}
