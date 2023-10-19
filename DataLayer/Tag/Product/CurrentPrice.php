<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag\Product;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Exception\NoSuchEntityException;
use AdPage\GTM\Api\Data\TagInterface;
use AdPage\GTM\Util\GetCurrentProduct;
use AdPage\GTM\Util\PriceFormatter;

class CurrentPrice implements TagInterface
{
    private GetCurrentProduct $getCurrentProduct;
    private PriceFormatter $priceFormatter;

    /**
     * @param GetCurrentProduct $getCurrentProduct
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        GetCurrentProduct $getCurrentProduct,
        PriceFormatter $priceFormatter
    ) {
        $this->getCurrentProduct = $getCurrentProduct;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * @return float
     * @throws NoSuchEntityException
     */
    public function get(): float
    {
        $product = $this->getCurrentProduct->get();
        return $this->priceFormatter->format(
            (float) $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue() // @phpstan-ignore-line
        );
    }
}
