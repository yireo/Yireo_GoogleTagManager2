<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use AdPage\GTM\DataLayer\Mapper\ProductDataMapper;
use AdPage\GTM\Api\Data\MergeTagInterface;
use AdPage\GTM\Util\GetCurrentProduct;

class CurrentProduct implements MergeTagInterface
{
    private ?ProductInterface $product = null;
    private GetCurrentProduct $getCurrentProduct;
    private ProductDataMapper $productDataMapper;

    /**
     * @param GetCurrentProduct $getCurrentProduct
     * @param ProductDataMapper $productDataMapper
     */
    public function __construct(
        GetCurrentProduct $getCurrentProduct,
        ProductDataMapper $productDataMapper
    ) {
        $this->getCurrentProduct = $getCurrentProduct;
        $this->productDataMapper = $productDataMapper;
    }

    /**
     * @return string[]
     * @throws NoSuchEntityException
     */
    public function merge(): array
    {
        return $this->productDataMapper->mapByProduct($this->getProduct());
    }

    /**
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProduct(): ProductInterface
    {
        if ($this->product instanceof ProductInterface) {
            return $this->product;
        }

        return $this->getCurrentProduct->get();
    }

    /**
     * @param ProductInterface $product
     * @return void
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
    }
}
