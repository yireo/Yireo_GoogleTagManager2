<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Event;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Tagging\GTM\Api\Data\EventInterface;
use Tagging\GTM\DataLayer\Mapper\ProductDataMapper;
use Tagging\GTM\DataLayer\Tag\CurrencyCode;
use Tagging\GTM\Util\PriceFormatter;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;

class AddToCart implements EventInterface
{
    private ProductDataMapper $productDataMapper;
    private CurrencyCode $currencyCode;
    private PriceFormatter $priceFormatter;
    private Product $product;
    private ProductRepositoryInterface $productRepository;
    private int $qty = 1;

    /**
     * @param ProductDataMapper $productDataMapper
     * @param CurrencyCode $currencyCode
     */
    public function __construct(
        ProductDataMapper $productDataMapper,
        CurrencyCode $currencyCode,
        PriceFormatter $priceFormatter,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productDataMapper = $productDataMapper;
        $this->currencyCode = $currencyCode;
        $this->priceFormatter = $priceFormatter;
        $this->productRepository = $productRepository;
    }

    /**
     * @return string[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function get(): array
    {
        $qty = ($this->qty > 0) ? $this->qty : 1;

        $product = $this->product;

        try {
            $product = $this->productRepository->get($this->product->getSku());
        } catch (Exception $e) {
            // Continue normal product flow since the sku is not found.
        }
        
        $itemData = $this->productDataMapper->mapByProduct($product);
        $itemData['quantity'] = $qty;
        $value = $itemData['price'] * $qty;

        return [
            'event' => 'trytagging_add_to_cart',
            'ecommerce' => [
                'currency' => $this->currencyCode->get(),
                'value' => $this->priceFormatter->format((float)$value),
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
