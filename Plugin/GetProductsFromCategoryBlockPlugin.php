<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Api\CategoryViewModelInterface;

class GetProductsFromCategoryBlockPlugin
{
    /**
     * @var CategoryViewModelInterface
     */
    private $categoryViewModel;

    /**
     * @var Config
     */
    private $config;

    /**
     * GetProductsFromCategoryBlockPlugin constructor.
     * @param CategoryViewModelInterface $categoryViewModel
     * @param Config $config
     */
    public function __construct(
        CategoryViewModelInterface $categoryViewModel,
        Config $config
    ) {
        $this->categoryViewModel = $categoryViewModel;
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
        $this->categoryViewModel->setCategorySize($collection->count());

        $i = 0;
        foreach ($collection as $product) {
            if ($i >= $this->config->getCategoryProducts()) {
                break;
            }

            $this->categoryViewModel->addCategoryProduct($product);
            $i++;
        }

        return $collection;
    }
}
