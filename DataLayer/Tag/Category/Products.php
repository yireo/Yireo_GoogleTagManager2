<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Category;

use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentCategory;
use Yireo\GoogleTagManager2\Util\GetCurrentCategoryProducts;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;

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
