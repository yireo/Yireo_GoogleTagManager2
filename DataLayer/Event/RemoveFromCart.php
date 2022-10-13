<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Quote\Api\Data\CartItemInterface;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CartItemDataMapper;

class RemoveFromCart implements EventInterface
{
    private CartItemDataMapper $cartItemDataMapper;

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
    public function get(CartItemInterface $cartItem): array
    {
        $cartItemData = $this->cartItemDataMapper->mapByCartItem($cartItem);
        return [
            'event' => 'remove_from_cart',
            'ecommerce' => [
                'items' => [$cartItemData]
            ]
        ];
    }
}
