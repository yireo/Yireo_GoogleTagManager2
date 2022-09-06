<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Api\Data\AttributeSetInterfaceFactory;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;

trait CreateProduct
{
    public function createProduct(
        int $id,
        array $data = []
    ): ProductInterface {
        $productFactory = ObjectManager::getInstance()->get(ProductInterfaceFactory::class);

        /** @var $product ProductModel */
        $product = $productFactory->create();
        $product->isObjectNew(true);
        $product->setId($id)
            ->setName('Product ' . $id)
            ->setSku('product' . $id)
            ->setWeight(10)
            ->setTypeId('simple')
            ->setPrice(10)
            ->setStatus(1)
            ->setStoreId(1)
            ->setWebsiteIds([1])
            ->setAttributeSetId($this->getDefaultAttributeSetId())
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->addData($data);

        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $productRepository->save($product);

        return $product;
    }

    public function createProducts($numberOfProducts = 1, array $data = []): array
    {
        $products = [];
        for ($i = 1; $i < $numberOfProducts + 1; $i++) {
            $products[] = $this->createProduct($i, $data);
        }

        return $products;
    }

    /**
     * @return int
     */
    private function getDefaultAttributeSetId(): int
    {
        $productFactory = ObjectManager::getInstance()->get(Product::class);
        return (int)$productFactory->getDefaultAttributeSetId();
    }
}
