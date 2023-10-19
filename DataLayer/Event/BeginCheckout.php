<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Event;

use Magento\Quote\Model\Quote;
use AdPage\GTM\Api\Data\EventInterface;
use AdPage\GTM\DataLayer\Tag\Cart\CartItems;
use AdPage\GTM\DataLayer\Tag\Cart\CartValue;
use AdPage\GTM\DataLayer\Tag\CurrencyCode;

class BeginCheckout implements EventInterface
{
    private Quote $quote;
    private CartItems $cartItems;
    private CartValue $cartValue;
    private CurrencyCode $currencyCode;

    /**
     * @param Quote $quote
     * @param CartItems $cartItems
     * @param CartValue $cartValue
     * @param CurrencyCode $currencyCode
     */
    public function __construct(
        Quote $quote,
        CartItems $cartItems,
        CartValue $cartValue,
        CurrencyCode $currencyCode
    ) {
        $this->quote = $quote;
        $this->cartItems = $cartItems;
        $this->cartValue = $cartValue;
        $this->currencyCode = $currencyCode;
    }

    public function get(): array
    {
        return [
            'event' => 'trytagging_begin_checkout',
            'ecommerce' => [
                'currency' => $this->currencyCode->get(),
                'value' => $this->cartValue->get(),
                'coupon' => $this->quote->getCouponCode(),
                'items' => $this->cartItems->get()
            ]
        ];
    }
}
