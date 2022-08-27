<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Ecommerce;

use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;
use Yireo\GoogleTagManager2\Util\GetProductsFromCategoryBlock;

class Value implements TagInterface
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

    public function get(): ?float
    {
        if (!$this->config->getIsEnabledEnhancedEcommercePushing()) {
            return null;
        }

        $productData = $this->getProductsInCategory->getProducts();

        $totalPrice = 0.00;
        foreach ($productData as $product) {
            $totalPrice += (isset($product['price'])) ? (float)$product['price'] : 0.00;
        }

        return $totalPrice;
    }
}
