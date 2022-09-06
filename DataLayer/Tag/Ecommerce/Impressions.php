<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Ecommerce;

use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;
use Yireo\GoogleTagManager2\Util\GetProductsFromCategoryBlock;

class Impressions implements TagInterface
{
    private GetProductsFromCategoryBlock $getProductsInCategory;
    private Config $config;

    public function __construct(
        GetProductsFromCategoryBlock $getProductsInCategory,
        Config $config
    ) {
        $this->getProductsInCategory = $getProductsInCategory;
        $this->config = $config;
    }

    public function get(): ?array
    {
        if (!$this->config->getIsEnabledEnhancedEcommercePushing()) {
            return null;
        }

        $products = [];
        foreach ($this->getProductsInCategory->getProducts() as $product) {
            $product['list'] = 'category';
            $products[] = $product;
        }

        return $products;
    }
}
