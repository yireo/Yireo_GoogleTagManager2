<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
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
        $product->setId($id)
            ->setName('Product ' . $id)
            ->setSku('product' . $id)
            ->setWeight(10)
            ->setTypeId(Type::TYPE_SIMPLE)
            ->setPrice(10)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStoreId(1)
            ->setWebsiteIds([1])
            ->setAttributeSetId($this->getDefaultAttributeSetId())
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStockData(['use_config_manage_stock' => 0])
            ->setCanSaveCustomOptions(true)
            ->setHasOptions(true)
            ->addData($data);

        $product->isObjectNew(true);

        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $productRepository->save($product);

        if (!empty($product->getCategoryIds())) {
            $categoryLinkManagement = ObjectManager::getInstance()->get(CategoryLinkManagementInterface::class);
            $categoryLinkManagement->assignProductToCategories(
                $product->getSku(),
                $product->getCategoryIds()
            );
        }

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
