<?php declare(strict_types=1);

namespace AdPage\GTM\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use AdPage\GTM\Util\GetCurrentCategoryProducts;
use AdPage\GTM\DataLayer\Tag\Category\CategorySize;
use AdPage\GTM\Config\Config;

class GetProductsFromCategoryBlockPlugin
{
    private CategorySize $categorySize;
    private GetCurrentCategoryProducts $getCurrentCategoryProducts;
    private Config $config;

    /**
     * GetProductsFromCategoryBlockPlugin constructor.
     * @param CategorySize $categorySize
     */
    public function __construct(
        CategorySize $categorySize,
        GetCurrentCategoryProducts $getCurrentCategoryProducts,
        Config $config
    ) {
        $this->categorySize = $categorySize;
        $this->getCurrentCategoryProducts = $getCurrentCategoryProducts;
        $this->config = $config;
    }

    /**
     * @param ListProduct $listProductBlock
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    public function afterGetLoadedProductCollection(
        ListProduct $listProductBlock,
        AbstractCollection $collection
    ): AbstractCollection {
        $maximumCategoryProducts = $this->config->getMaximumCategoryProducts();
        if ($maximumCategoryProducts <= 0) {
            return $collection;
        }
        $i = 0;
        foreach ($collection as $product) {
            if ($i > $maximumCategoryProducts) {
                break;
            }

            $this->getCurrentCategoryProducts->addProduct($product);
            $i++;
        }

        $this->categorySize->setSize($collection->count());
        return $collection;
    }
}
