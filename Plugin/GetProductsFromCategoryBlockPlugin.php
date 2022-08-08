<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\GetCurrentCategoryProducts;
use Yireo\GoogleTagManager2\DataLayer\Tag\Category\CategorySize;

class GetProductsFromCategoryBlockPlugin
{
    private Config $config;
    private CategorySize $categorySize;
    private GetCurrentCategoryProducts $getCurrentCategoryProducts;

    /**
     * GetProductsFromCategoryBlockPlugin constructor.
     * @param Config $config
     * @param CategorySize $categorySize
     */
    public function __construct(
        Config $config,
        CategorySize $categorySize,
        GetCurrentCategoryProducts $getCurrentCategoryProducts
    ) {
        $this->config = $config;
        $this->categorySize = $categorySize;
        $this->getCurrentCategoryProducts = $getCurrentCategoryProducts;
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
        $i = 0;
        foreach ($collection as $product) {
            // @todo: Add a setting for this?

            $this->getCurrentCategoryProducts->addProduct($product);
            $i++;
        }

        $this->categorySize->setSize($collection->count());
        return $collection;
    }
}
