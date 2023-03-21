<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartItems;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartValue;
use Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode;

class ViewCart implements EventInterface
{
    private CartItems $cartItems;
    private CartValue $cartValue;
    private CurrencyCode $currencyCode;

    /**
     * @param CartItems $cartItems
     * @param CartValue $cartValue
     * @param CurrencyCode $currencyCode
     */
    public function __construct(
        CartItems $cartItems,
        CartValue $cartValue,
        CurrencyCode $currencyCode
    ) {
        $this->cartItems = $cartItems;
        $this->cartValue = $cartValue;
        $this->currencyCode = $currencyCode;
    }

    /**
     * @return string[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function get(): array
    {
        return [
            'cacheable' => true,
            'event' => 'view_cart',
            'ecommerce' => [
                'currency' => $this->currencyCode->get(),
                'value' => $this->cartValue->get(),
                'items' => $this->cartItems->get()
            ]
        ];
    }
}
