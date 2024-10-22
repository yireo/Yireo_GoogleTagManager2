<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Event;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Event\Purchase;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateProduct;

/**
 * @magentoAppArea frontend
 */
class PurchaseTest extends TestCase
{
    use CreateProduct;

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testValidDataLayerWithCart()
    {
        $purchaseEvent = ObjectManager::getInstance()->get(Purchase::class);
        $data = $purchaseEvent->setOrder($this->getOrder())->get();

        $this->assertNotEmpty($data);
        $this->assertCount(1, $data['ecommerce']['items']);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     * @magentoConfigFixture current_store googletagmanager2/settings/order_states_for_purchase_event payment_review,pending_payment,holded,processing,complete
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testValidDataLayerWithCartWithSpecificOrderStates()
    {
        $purchaseEvent = ObjectManager::getInstance()->get(Purchase::class);
        $data = $purchaseEvent->setOrder($this->getOrder())->get();

        $this->assertNotEmpty($data);
        $this->assertCount(1, $data['ecommerce']['items']);
    }

    private function getOrder(): OrderInterface
    {
        $orderRepository = ObjectManager::getInstance()->get(OrderRepositoryInterface::class);
        $searchCriteriaBuilder = ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->setPageSize(1);
        $searchResults = $orderRepository->getList($searchCriteriaBuilder->create());
        $items = $searchResults->getItems();
        return array_shift($items);
    }
}
