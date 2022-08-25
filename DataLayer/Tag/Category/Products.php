<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Category;

use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Tag\AddTagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentCategory;
use Yireo\GoogleTagManager2\Util\GetCurrentCategoryProducts;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;

class Products implements AddTagInterface
{
    private GetCurrentCategoryProducts $getCurrentCategoryProducts;
    private GetCurrentCategory $getCurrentCategory;
    private ProductDataMapper $productDataMapper;
    private Config $config;

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

    public function addData(): array
    {
        $productsData = [];
        $i = 1;
        foreach ($this->getCurrentCategoryProducts->getProducts() as $product) {
            if ($this->config->getMaximumCategoryProducts() > 0 && $i > $this->config->getMaximumCategoryProducts()) {
                break;
            }

            $product->setCategory($this->getCurrentCategory->get());
            $productData = $this->productDataMapper->mapByProduct($product);
            $productData['category'] = $this->getCurrentCategory->get()->getName();
            $productData['position'] = $i;
            $productsData[] = $productData;
            $i++;
        }

        return $productsData;
    }
}
