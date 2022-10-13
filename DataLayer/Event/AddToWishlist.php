<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Catalog\Api\Data\ProductInterface;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;

/**
 * @todo Implement this class
 */
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
     * @param ProductInterface[] $products
     * @return string[]
     */
    public function get(array $products): array
    {
        $itemsData = [];
        foreach ($products as $product) {
            $itemData = $this->productDataMapper->mapByProduct($product);
            $itemsData[] = $itemData;
        }

        return [
            'event' => 'add_to_wishlist',
            'ecommerce' => [
                'items' => $itemsData
            ]
        ];
    }
}
