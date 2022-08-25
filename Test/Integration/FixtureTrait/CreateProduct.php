<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\App\ObjectManager;

trait CreateProduct
{
    public function createProduct(
        int $id,
    ): ProductInterface {
        $productFactory = ObjectManager::getInstance()->get(ProductInterfaceFactory::class);

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productFactory->create();
        $product->isObjectNew(true);
        $product->setId($id)
            ->setName('Product ' . $id)
            ->setSku('product' . $id)
            ->save();

        return $product;
    }

    public function createProducts($numberOfProducts = 1): array
    {
        $products = [];
        for ($i = 1; $i < $numberOfProducts + 1; $i++) {
            $products[] = $this->createProduct($i);
        }

        return $products;
    }
}
