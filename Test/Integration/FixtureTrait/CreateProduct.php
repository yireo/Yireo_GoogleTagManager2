<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Test\Integration\Stub\FulltextStub;

trait CreateProduct
{
    public function getProduct($productId = 0, array $data = []): ProductInterface
    {
        $objectManager = ObjectManager::getInstance();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);

        try {
            $product = $productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = $this->createProduct($productId, $data);
        }

        $this->assertTrue($product->getId() > 0, 'No product ID given');
        $this->assertTrue($product->isSalable(), 'Product is not salable');

        return $product;
    }

    public function createProduct(
        int $productId,
        array $data = []
    ): ProductInterface {
        $objectManager = ObjectManager::getInstance();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);

        $objectManager->configure([
            'preferences' => [
                Fulltext::class => FulltextStub::class
            ]
        ]);

        //$this->disableEventObserver('catalog_product_save_after', ProductProcessUrlRewriteSavingObserver::class);

        $productSku = 'simple-product-'.rand(1,1000000);
        $product = $objectManager->create(ProductInterface::class);

        if ($productId > 0) {
            $product->setId($productId);
        }

        $product->addData([
            'type_id' => Type::TYPE_SIMPLE,
            'attribute_set_id' => 4,
            'name' => 'Simple Product '.rand(1,1000000),
            'sku' => $productSku,
            'price' => 10,
            'weight' => 1,
            'visibility' => Visibility::VISIBILITY_BOTH,
            'status' => Status::STATUS_ENABLED,
        ]);

        $product->addData($data);

        $product->setCustomAttribute('tax_class_id', '2');
        $product->getExtensionAttributes()->setWebsiteIds([1]);
        $product->isObjectNew(true);

        $productRepository->save($product);

        $stockRegistry = $objectManager->get(StockRegistryInterface::class);
        $stockItem = $stockRegistry->getStockItemBySku($product->getSku());
        $stockItem->setUseConfigManageStock(1);
        $stockItem->setIsInStock(1);
        $stockItem->setProductId($product->getId());
        $stockItem->setQty(9999);
        $stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);

        if (!empty($product->getCategoryIds())) {
            $categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);
            $categoryLinkManagement->assignProductToCategories(
                $product->getSku(),
                $product->getCategoryIds()
            );
        }

        $product = $productRepository->get($productSku);

        $productHelper = $objectManager->get(\Magento\Catalog\Helper\Product::class);
        $productHelper->setSkipSaleableCheck(true);

        $this->assertTrue($product->getId() > 0, 'No product ID given');
        $this->assertTrue($product->isSalable(), 'Product is not salable');

        return $product;
    }

    public function getProducts($numberOfProducts = 1, array $data = []): array
    {
        $products = [];
        for ($i = 1; $i < $numberOfProducts + 1; $i++) {
            $products[] = $this->getProduct($i, $data);
        }

        return $products;
    }

    /*
    private function deleteProduct(int $id)
    {
        $objectManager = ObjectManager::getInstance();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);

        try {
            $product = $productRepository->getById($id);
            $objectManager->get(Registry::class)->register('isSecureArea', true);
            $productRepository->delete($product);

            $resourceConnection = $objectManager->get(ResourceConnection::class);
            $connection = $resourceConnection->getConnection();
            $connection->query(
                'DELETE FROM `'.$connection->getTableName(
                    'url_rewrite'
                ).'` WHERE entity_type="product" AND entity_id='.$id
            );
            $connection->query(
                'DELETE FROM `'.$connection->getTableName(
                    'catalog_url_rewrite_product_category'
                ).'` WHERE product_id='.$id
            );
        } catch (NoSuchEntityException $e) {
        }
    }

    private function disableEventObserver(string $eventName, string $observerClass): void
    {
        $objectManager = ObjectManager::getInstance();
        $eventConfig = $objectManager->get(ConfigInterface::class);
        $dataContainer = $objectManager->get(Data::class);
        $observers = $eventConfig->getObservers($eventName);
        foreach ($observers as $name => $data) {
            if ($data['instance'] === $observerClass) {
                $data['disabled'] = true;
                $dataContainer->merge([
                    $eventName => [
                        $name => $data
                    ]
                ]);
                break;
            }
        }
    }

    private function getProductRepository(): ProductRepositoryInterface
    {
        return ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
    }*/
}
