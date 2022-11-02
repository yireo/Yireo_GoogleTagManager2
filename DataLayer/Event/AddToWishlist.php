<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;

class AddToWishlist implements EventInterface
{
    private ProductDataMapper $productDataMapper;

    /**
     * @param ProductDataMapper $productDataMapper
     */
    public function __construct(
        ProductDataMapper $productDataMapper
    ) {
        $this->productDataMapper = $productDataMapper;
    }

    /**
     * @param ProductInterface $product
     * @return string[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function get(ProductInterface $product): array
    {
        $itemData = $this->productDataMapper->mapByProduct($product);

        return [
            'event' => 'add_to_wishlist',
            'ecommerce' => [
                'items' => [$itemData]
            ]
        ];
    }
}
