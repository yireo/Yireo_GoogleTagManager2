<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote as Cart;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartItems;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class AddPaymentInfo implements EventInterface
{
    private CartItems $cartItems;
    private CartRepositoryInterface $cartRepository;
    private PriceFormatter $priceFormatter;
    private int $cartId;
    private string $paymentMethod;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CartItems $cartItems
     */
    public function __construct(
        CartRepositoryInterface  $cartRepository,
        CartItems $cartItems,
        PriceFormatter $priceFormatter
    ) {
        $this->cartItems = $cartItems;
        $this->cartRepository = $cartRepository;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        /** @var Cart $cart */
        $cart = $this->cartRepository->get($this->cartId);

        return [
            'event' => 'add_payment_info',
            'ecommerce' => [
                'currency' => $cart->getQuoteCurrencyCode(),
                'value' => $this->priceFormatter->format((float)$cart->getSubtotal()),
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
