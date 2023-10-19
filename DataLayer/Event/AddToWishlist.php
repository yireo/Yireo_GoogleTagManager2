<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Event;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use AdPage\GTM\Api\Data\EventInterface;
use AdPage\GTM\DataLayer\Mapper\ProductDataMapper;

class AddToWishlist implements EventInterface
{
    private ProductDataMapper $productDataMapper;
    private ProductInterface $product;

    /**
     * @param ProductDataMapper $productDataMapper
     */
    public function __construct(
        ProductDataMapper $productDataMapper
    ) {
        $this->productDataMapper = $productDataMapper;
    }

    /**
     * @return string[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function get(): array
    {
        $itemData = $this->productDataMapper->mapByProduct($this->product);

        return [
            'event' => 'trytagging_add_to_wishlist',
            'ecommerce' => [
                'items' => [$itemData]
            ]
        ];
    }

    /**
     * @param ProductInterface $product
     * @return AddToWishlist
     */
    public function setProduct(ProductInterface $product): AddToWishlist
    {
        $this->product = $product;
        return $this;
    }
}
