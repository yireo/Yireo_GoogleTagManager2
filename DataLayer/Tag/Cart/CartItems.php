<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Cart;

use Magento\Checkout\Model\Cart as CartModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CartItemDataMapper;

class CartItems implements TagInterface
{
    private CartModel $cartModel;
    private CartItemDataMapper $cartItemDataMapper;
    
    /**
     * @param CartModel $cartModel
     * @param CartItemDataMapper $cartItemDataMapper
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function __construct(
        CartModel $cartModel,
        CartItemDataMapper $cartItemDataMapper
    ) {
        $this->cartModel = $cartModel;
        $this->cartItemDataMapper = $cartItemDataMapper;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function get(): array
    {
        $cartItems = $this->cartModel->getQuote()->getItems();
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
