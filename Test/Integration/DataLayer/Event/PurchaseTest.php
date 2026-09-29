<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Event;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Event\Purchase;

/**
 * @magentoAppArea frontend
 */
class PurchaseTest extends TestCase
{
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

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 0
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testValueAdjustedEqualsValueWithoutMaximum()
    {
        $ecommerce = $this->getEcommerceData();

        $this->assertSame(90.0, $ecommerce['value']);
        $this->assertSame(90.0, $ecommerce['value_adjusted']);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 50
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testValueAdjustedIsCappedButValueIsNot()
    {
        $ecommerce = $this->getEcommerceData();

        $this->assertSame(90.0, $ecommerce['value']);
        $this->assertSame(50.0, $ecommerce['value_adjusted']);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 0
     * @magentoConfigFixture current_store googletagmanager2/settings/include_shipping_in_adjusted_value 1
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testValueAdjustedIncludesShippingWhenEnabled()
    {
        $ecommerce = $this->getEcommerceData();

        $this->assertSame(90.0, $ecommerce['value']);
        $this->assertSame(20.0, $ecommerce['shipping']);
        $this->assertSame(110.0, $ecommerce['value_adjusted']);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/use_base_currency 0
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testValueAdjustedCurrencyIsOrderCurrency()
    {
        $ecommerce = $this->getEcommerceData();

        $this->assertArrayHasKey('value_adjusted_currency', $ecommerce);
        $this->assertSame('USD', $ecommerce['value_adjusted_currency']);
        $this->assertSame('USD', $ecommerce['currency']);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/use_base_currency 1
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 0
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testBaseCurrencyOnlyAppliesToValueNotToValueAdjusted()
    {
        $ecommerce = $this->getEcommerceData();

        $this->assertSame('EUR', $ecommerce['currency']);
        $this->assertSame(45.0, $ecommerce['value']);
        $this->assertSame(10.0, $ecommerce['shipping']);
        $this->assertSame(4.0, $ecommerce['tax']);

        $this->assertSame(90.0, $ecommerce['value_adjusted'], 'value_adjusted should be in order currency');
        $this->assertArrayHasKey('value_adjusted_currency', $ecommerce);
        $this->assertSame('USD', $ecommerce['value_adjusted_currency']);
    }

    private function getEcommerceData(): array
    {
        $order = $this->getOrder();
        $order->setOrderCurrencyCode('USD');
        $order->setBaseCurrencyCode('EUR');
        $order->setSubtotal(100);
        $order->setDiscountAmount(-10);
        $order->setShippingAmount(20);
        $order->setShippingDiscountAmount(0);
        $order->setTaxAmount(8);
        $order->setBaseSubtotal(50);
        $order->setBaseDiscountAmount(-5);
        $order->setBaseShippingAmount(10);
        $order->setBaseShippingDiscountAmount(0);
        $order->setBaseTaxAmount(4);

        $purchaseEvent = ObjectManager::getInstance()->create(Purchase::class);
        $data = $purchaseEvent->setOrder($order)->get();
        $this->assertArrayHasKey('ecommerce', $data);

        return $data['ecommerce'];
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
