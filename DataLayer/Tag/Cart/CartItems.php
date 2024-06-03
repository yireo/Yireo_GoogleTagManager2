<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Tag\Cart;

use Magento\Checkout\Model\Cart as CartModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Tagging\GTM\Api\Data\TagInterface;
use Tagging\GTM\DataLayer\Mapper\CartItemDataMapper;
use Tagging\GTM\Util\ProductProvider;

class CartItems implements TagInterface
{
    private CartModel $cartModel;
    private CartItemDataMapper $cartItemDataMapper;
    private ProductProvider $productProvider;

    /**
     * @param CartModel $cartModel
     * @param CartItemDataMapper $cartItemDataMapper
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function __construct(
        CartModel $cartModel,
        CartItemDataMapper $cartItemDataMapper,
        ProductProvider $productProvider
    ) {
        $this->cartModel = $cartModel;
        $this->cartItemDataMapper = $cartItemDataMapper;
        $this->productProvider = $productProvider;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function get(): array
    {
        $cartItems = $this->cartModel->getQuote()->getAllVisibleItems();
        if (!$cartItems) {
            return [];
        }

        $this->productProvider->addProductSkus($this->getSkusFromCartItems($cartItems));
        $cartItemsData = [];

        foreach ($cartItems as $cartItem) {
            $cartItemData = $this->cartItemDataMapper->mapByCartItem($cartItem);
            $cartItemsData[] = $cartItemData;
        }

        return $cartItemsData;
    }

    /**
     * @param Item[] $cartItems
     * @return array
     */
    private function getSkusFromCartItems(array $cartItems): array
    {
        $productSkus = [];
        foreach ($cartItems as $cartItem) {
            $productSkus[] = $cartItem->getSku();
        }

        return array_unique($productSkus);
    }
}
