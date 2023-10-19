<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Event;

use Magento\Quote\Api\Data\CartItemInterface;
use AdPage\GTM\Api\Data\EventInterface;
use AdPage\GTM\DataLayer\Mapper\CartItemDataMapper;

class RemoveFromCart implements EventInterface
{
    private CartItemDataMapper $cartItemDataMapper;
    private CartItemInterface $cartItem;

    /**
     * @param CartItemDataMapper $cartItemDataMapper
     */
    public function __construct(CartItemDataMapper $cartItemDataMapper)
    {
        $this->cartItemDataMapper = $cartItemDataMapper;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $cartItemData = $this->cartItemDataMapper->mapByCartItem($this->cartItem);
        return [
            'event' => 'trytagging_remove_from_cart',
            'ecommerce' => [
                'items' => [$cartItemData]
            ]
        ];
    }

    /**
     * @param CartItemInterface $cartItem
     * @return RemoveFromCart
     */
    public function setCartItem(CartItemInterface $cartItem): RemoveFromCart
    {
        $this->cartItem = $cartItem;
        return $this;
    }
}
