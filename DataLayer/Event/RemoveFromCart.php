<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Quote\Api\Data\CartItemInterface;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CartItemDataMapper;

class RemoveFromCart implements EventInterface
{
    private CartItemDataMapper $cartItemDataMapper;
    private CartItemInterface $cartItem;

    /**
     * @param CartItemDataMapper $cartItemDataMapper
     */
    public function __construct(CartItemDataMapper $cartItemDataMapper) {
        $this->cartItemDataMapper = $cartItemDataMapper;
    }

    /**
     * @param CartItemInterface $cartItem
     * @return array
     */
    public function get(): array
    {
        $cartItemData = $this->cartItemDataMapper->mapByCartItem($this->cartItem);
        return [
            'event' => 'remove_from_cart',
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
