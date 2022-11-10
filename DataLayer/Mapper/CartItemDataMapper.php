<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class CartItemDataMapper
{
    private ProductDataMapper $productDataMapper;
    private ProductRepositoryInterface $productRepository;
    private PriceFormatter $priceFormatter;

    /**
     * @param ProductDataMapper $productDataMapper
     * @param ProductRepositoryInterface $productRepository
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        ProductDataMapper $productDataMapper,
        ProductRepositoryInterface $productRepository,
        PriceFormatter $priceFormatter
    ) {
        $this->productDataMapper = $productDataMapper;
        $this->productRepository = $productRepository;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * @param CartItemInterface $cartItem
     * @return array
     */
    public function mapByCartItem(CartItemInterface $cartItem): array
    {
        $cartItemData = [
            'item_id' => $cartItem->getId(),
            'item_name' => $cartItem->getName(),
            'quantity' => $cartItem->getQty(),
            'price' => $this->priceFormatter->format((float)$cartItem->getPrice())
        ];

        try {
            $product = $this->productRepository->get($cartItem->getSku());
        } catch (NoSuchEntityException $e) {
            return $cartItemData;
        }

        return array_merge($this->productDataMapper->mapByProduct($product), $cartItemData);
    }
}
