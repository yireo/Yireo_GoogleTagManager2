<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;
use Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode;

class AddToCart implements EventInterface
{
    private ProductDataMapper $productDataMapper;
    private Product $product;
    private int $qty = 1;
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
        $qty = ($this->qty > 0) ? $this->qty : 1;

        $itemData = $this->productDataMapper->mapByProduct($this->product);
        $itemData['quantity'] = $qty;
        $value = $itemData['price'] * $qty;

        return [
            'event' => 'add_to_cart',
            'ecommerce' => [
                'currency' => $this->currencyCode->get(),
                'value' => $value,
                'items' => [$itemData]
            ]
        ];
    }

    /**
     * @param Product $product
     * @return AddToCart
     */
    public function setProduct(Product $product): AddToCart
    {
        $this->product = $product;
        return $this;
    }

    public function setQty(int $qty): AddToCart
    {
        $this->qty = $qty;
        return $this;
    }
}
