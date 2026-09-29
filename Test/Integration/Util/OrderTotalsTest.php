<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Util;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Util\OrderTotals;

/**
 * @magentoAppArea frontend
 */
class OrderTotalsTest extends TestCase
{
    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 0
     */
    public function testValueTotalAdjustedWithoutMaximum(): void
    {
        $this->assertSame(90.0, $this->getOrderTotals()->getValueTotalAjusted($this->createOrder()));
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 50
     */
    public function testValueTotalAdjustedIsCappedByMaximum(): void
    {
        $this->assertSame(50.0, $this->getOrderTotals()->getValueTotalAjusted($this->createOrder()));
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 500
     */
    public function testValueTotalAdjustedBelowMaximumIsUntouched(): void
    {
        $this->assertSame(90.0, $this->getOrderTotals()->getValueTotalAjusted($this->createOrder()));
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 0
     */
    public function testValueTotalAdjustedExcludesShippingByDefault(): void
    {
        $this->assertSame(90.0, $this->getOrderTotals()->getValueTotalAjusted($this->createOrder()));
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 0
     * @magentoConfigFixture current_store googletagmanager2/settings/include_shipping_in_adjusted_value 1
     */
    public function testValueTotalAdjustedIncludesShippingWhenEnabled(): void
    {
        $this->assertSame(110.0, $this->getOrderTotals()->getValueTotalAjusted($this->createOrder()));
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 100
     * @magentoConfigFixture current_store googletagmanager2/settings/include_shipping_in_adjusted_value 1
     */
    public function testMaximumIsAppliedAfterAddingShipping(): void
    {
        $this->assertSame(100.0, $this->getOrderTotals()->getValueTotalAjusted($this->createOrder()));
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/use_base_currency 1
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 0
     */
    public function testValueTotalAdjustedAlwaysUsesOrderCurrency(): void
    {
        $orderTotals = $this->getOrderTotals();
        $order = $this->createOrder();

        $this->assertSame(45.0, $orderTotals->getValueTotal($order));
        $this->assertSame(90.0, $orderTotals->getValueTotalAjusted($order));
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/use_base_currency 1
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 0
     * @magentoConfigFixture current_store googletagmanager2/settings/include_shipping_in_adjusted_value 1
     */
    public function testValueTotalAdjustedWithShippingAlwaysUsesOrderCurrency(): void
    {
        $this->assertSame(110.0, $this->getOrderTotals()->getValueTotalAjusted($this->createOrder()));
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/use_base_currency 0
     */
    public function testValueAndShippingTotalInOrderCurrency(): void
    {
        $orderTotals = $this->getOrderTotals();
        $order = $this->createOrder();

        $this->assertSame(90.0, $orderTotals->getValueTotal($order));
        $this->assertSame(20.0, $orderTotals->getShippingTotal($order));
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/use_base_currency 1
     */
    public function testValueAndShippingTotalInBaseCurrency(): void
    {
        $orderTotals = $this->getOrderTotals();
        $order = $this->createOrder();

        $this->assertSame(45.0, $orderTotals->getValueTotal($order));
        $this->assertSame(10.0, $orderTotals->getShippingTotal($order));
    }

    /**
     * 2 x product "simple" (10.00), flat rate shipping (5.00 per item), 10% cart rule that also applies to shipping
     *
     * @magentoConfigFixture current_store googletagmanager2/settings/max_transaction_value 0
     * @magentoConfigFixture current_store googletagmanager2/settings/include_shipping_in_adjusted_value 1
     * @magentoConfigFixture current_store carriers/flatrate/active 1
     * @magentoConfigFixture current_store carriers/flatrate/type I
     * @magentoConfigFixture current_store carriers/flatrate/price 5
     * @magentoConfigFixture current_store payment/checkmo/active 1
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off_with_discount_on_shipping.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testShippingDiscountIsNotSubtractedTwice(): void
    {
        $order = $this->placeOrder();

        $this->assertEquals(20.0, (float)$order->getSubtotal());
        $this->assertEquals(10.0, (float)$order->getShippingAmount());
        $this->assertEquals(1.0, (float)$order->getShippingDiscountAmount());
        $this->assertEquals(-3.0, (float)$order->getDiscountAmount(), 'discount_amount includes the shipping discount');
        $this->assertEquals(27.0, (float)$order->getGrandTotal());

        $this->assertSame(
            27.0,
            $this->getOrderTotals()->getValueTotalAjusted($order),
            'Adjusted value including shipping should equal what the customer paid (excl. tax)'
        );
    }

    private function placeOrder(): OrderInterface
    {
        $objectManager = ObjectManager::getInstance();
        $quote = $objectManager->create(Quote::class);
        $objectManager->get(QuoteResource::class)->load($quote, 'test_order_1', 'reserved_order_id');

        $quote->getShippingAddress()
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate');
        $quote->getPayment()->setMethod('checkmo');
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $objectManager->get(CartRepositoryInterface::class)->save($quote);

        return $objectManager->get(QuoteManagement::class)->submit($quote);
    }

    private function createOrder(): OrderInterface
    {
        $order = ObjectManager::getInstance()->create(Order::class);
        $order->setData([
            'order_currency_code' => 'USD',
            'base_currency_code' => 'EUR',
            'subtotal' => 100,
            'discount_amount' => -10,
            'shipping_amount' => 20,
            'shipping_discount_amount' => 0,
            'base_subtotal' => 50,
            'base_discount_amount' => -5,
            'base_shipping_amount' => 10,
            'base_shipping_discount_amount' => 0,
        ]);

        return $order;
    }

    private function getOrderTotals(): OrderTotals
    {
        return ObjectManager::getInstance()->get(OrderTotals::class);
    }
}
