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
use Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Event\Config\Data;
use Magento\Framework\Event\ConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Registry;
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

        $this->disableEventObserver('catalog_product_save_after', ProductProcessUrlRewriteSavingObserver::class);
        $this->deleteProduct($id);

        /** @var $product ProductModel */
        $product = $productFactory->create();
        $product->isObjectNew(true);
        $product
            ->setId($id)
            ->setName('Product '.$id)
            ->setSku('product'.$id)
            ->setUrlKey('product'.$id)
            ->setUrlPath('product'.$id)
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
            ->addData($data);

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

        return (int)$websiteRepository->get('base')->getId();
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
}
