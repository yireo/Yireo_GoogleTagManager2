<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Quote\Api\Data\CartItemInterface;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CartItemDataMapper;
use Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class RemoveFromCart implements EventInterface
{
    private ?CartItemInterface $cartItem = null;
    private CartItemDataMapper $cartItemDataMapper;
    private CurrencyCode $currencyCode;
    private PriceFormatter $priceFormatter;

    /**
     * @param CartItemDataMapper $cartItemDataMapper
     */
    public function __construct(
        CartItemDataMapper $cartItemDataMapper,
        CurrencyCode $currencyCode,
        PriceFormatter $priceFormatter
    ) {
        $this->cartItemDataMapper = $cartItemDataMapper;
        $this->currencyCode = $currencyCode;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $itemData = $this->cartItemDataMapper->mapByCartItem($this->cartItem);
        $value = $itemData['price'] * $itemData['quantity'];

        return [
            'event' => 'remove_from_cart',
            'ecommerce' => [
                'currency' => $this->currencyCode->get(),
                'value'    => $this->priceFormatter->format((float)$value),
                'items'    => [$itemData]
            ]
        ];
    }

    /**
     * @param CartItemInterface $cartItem
     *
     * @return RemoveFromCart
     */
    public function setCartItem(CartItemInterface $cartItem): RemoveFromCart
    {
        $this->cartItem = $cartItem;
        return $this;
    }
}
