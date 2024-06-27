<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

trait CreateProduct
{
    public function createProduct(
        int $id,
        array $data = []
    ): ProductInterface {
        $objectManager = ObjectManager::getInstance();
        $productFactory = $objectManager->get(ProductInterfaceFactory::class);
        $defaultCategory = $objectManager->get(DefaultCategory::class);
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);

        /** @var $product ProductModel */
        $product = $productFactory->create();
        $product->isObjectNew(true);
        $product
            ->setId($id)
            ->setName('Product ' . $id)
            ->setSku('product' . $id)
            ->setUrlKey('product' . $id)
            ->setUrlPath('product' . $id)
            ->setWeight(10)
            ->setCategoryIds([$defaultCategory->getId()])
            ->setTypeId(Type::TYPE_SIMPLE)
            ->setPrice(10)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStoreId(0)
            ->setWebsiteIds([$this->getDefaultWebsiteId()])
            ->setAttributeSetId($this->getDefaultAttributeSetId())
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStockData(['use_config_manage_stock' => 0])
            //->setCanSaveCustomOptions(true)
            //->setHasOptions(true)
            ->addData($data)
        ;

        $product->isObjectNew(true);
        $productRepository->save($product);

        if (!empty($product->getCategoryIds())) {
            $categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);
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

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    private function getDefaultWebsiteId(): int
    {
        $objectManager = ObjectManager::getInstance();
        $websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
        return (int) $websiteRepository->get('base')->getId();
    }
}
