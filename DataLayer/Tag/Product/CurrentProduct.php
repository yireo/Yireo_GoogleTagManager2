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
    private string $prefix;

    /**
     * @param GetCurrentProduct $getCurrentProduct
     * @param ProductDataMapper $productDataMapper
     * @param string $prefix
     */
    public function __construct(
        GetCurrentProduct $getCurrentProduct,
        ProductDataMapper $productDataMapper,
        string $prefix = ''
    ) {
        $this->getCurrentProduct = $getCurrentProduct;
        $this->productDataMapper = $productDataMapper;
        $this->prefix = $prefix;
    }

    /**
     * @return string[]
     * @throws NoSuchEntityException
     */
    public function merge(): array
    {
        $currentProduct = $this->getCurrentProduct->get();
        return $this->productDataMapper->mapByProduct($currentProduct, $this->prefix);
    }
}
