<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;
use Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode;

class AddToCart implements EventInterface
{
    private ProductDataMapper $productDataMapper;
    private ProductInterface $product;
    private CurrencyCode $currencyCode;

    /**
     * @param ProductDataMapper $productDataMapper
     * @param CurrencyCode $currencyCode
     */
    public function __construct(
        ProductDataMapper $productDataMapper,
        CurrencyCode $currencyCode
    ) {
        $this->productDataMapper = $productDataMapper;
        $this->currencyCode = $currencyCode;
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
            'event' => 'add_to_cart',
            'currencyCode' => $this->currencyCode->get(),
            'ecommerce' => [
                'items' => [$itemData]
            ]
        ];
    }

    /**
     * @param ProductInterface $product
     * @return AddToCart
     */
    public function setProduct(ProductInterface $product): AddToCart
    {
        $this->product = $product;
        return $this;
    }
}
