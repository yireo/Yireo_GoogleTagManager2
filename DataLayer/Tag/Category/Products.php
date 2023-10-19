<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag\Category;

use Magento\Framework\Exception\NoSuchEntityException;
use AdPage\GTM\Config\Config;
use AdPage\GTM\Api\Data\TagInterface;
use AdPage\GTM\Util\GetCurrentCategory;
use AdPage\GTM\Util\GetCurrentCategoryProducts;
use AdPage\GTM\DataLayer\Mapper\ProductDataMapper;

class Products implements TagInterface
{
    private GetCurrentCategoryProducts $getCurrentCategoryProducts;
    private GetCurrentCategory $getCurrentCategory;
    private ProductDataMapper $productDataMapper;
    private Config $config;

    /**
     * @param GetCurrentCategoryProducts $getCurrentCategoryProducts
     * @param GetCurrentCategory $getCurrentCategory
     * @param ProductDataMapper $productDataMapper
     * @param Config $config
     */
    public function __construct(
        GetCurrentCategoryProducts $getCurrentCategoryProducts,
        GetCurrentCategory $getCurrentCategory,
        ProductDataMapper $productDataMapper,
        Config $config
    ) {
        $this->getCurrentCategoryProducts = $getCurrentCategoryProducts;
        $this->getCurrentCategory = $getCurrentCategory;
        $this->productDataMapper = $productDataMapper;
        $this->config = $config;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function get(): array
    {
        $productsData = [];
        $i = 1;
        foreach ($this->getCurrentCategoryProducts->getProducts() as $product) {
            if ($this->config->getMaximumCategoryProducts() > 0 && $i > $this->config->getMaximumCategoryProducts()) {
                break;
            }

            $product->setCategory($this->getCurrentCategory->get());
            $productData = $this->productDataMapper->mapByProduct($product);
            $productData['quantity'] = 1;
            $productData['index'] = $i;
            $productsData[] = $productData;
            $i++;
        }

        return $productsData;
    }
}
