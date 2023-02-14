<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Cart;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CartItemDataMapper;

class CartItems implements TagInterface
{
    private CartInterface $cart;
    private CartItemDataMapper $cartItemDataMapper;

    /**
     * @param CartItemDataMapper $cartItemDataMapper
     * @param Session $session
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function __construct(
        Session $session,
        CartItemDataMapper $cartItemDataMapper
    ) {
        $this->cart = $session->getQuote();
        $this->cartItemDataMapper = $cartItemDataMapper;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function get(): array
    {
        $cartItems = $this->cart->getItems();
        if (!$cartItems) {
            return [];
        }

        $cartItemsData = [];
        foreach ($cartItems as $cartItem) {
            $cartItemData = $this->cartItemDataMapper->mapByCartItem($cartItem);
            $cartItemsData[] = $cartItemData;
        }

        return $cartItemsData;
    }
}
