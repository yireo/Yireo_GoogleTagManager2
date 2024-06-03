<?php declare(strict_types=1);

namespace Tagging\GTM\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Tagging\GTM\Util\GetCurrentCategoryProducts;
use Tagging\GTM\DataLayer\Tag\Category\CategorySize;
use Tagging\GTM\Config\Config;

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
        $maximumCategoryProducts = 50;
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
