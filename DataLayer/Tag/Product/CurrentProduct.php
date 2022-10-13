<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;
use Yireo\GoogleTagManager2\Api\Data\MergeTagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;

class CurrentProduct implements MergeTagInterface
{
    private GetCurrentProduct $getCurrentProduct;
    private ProductDataMapper $productDataMapper;

    /**
     * @param GetCurrentProduct $getCurrentProduct
     * @param ProductDataMapper $productDataMapper
     */
    public function __construct(
        GetCurrentProduct $getCurrentProduct,
        ProductDataMapper $productDataMapper,
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
        $currentProduct = $this->getCurrentProduct->get();
        return $this->productDataMapper->mapByProduct($currentProduct);
    }
}
