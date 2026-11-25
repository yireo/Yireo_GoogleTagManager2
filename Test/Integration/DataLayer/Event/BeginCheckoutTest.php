<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Event;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\QuoteFactory;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Event\BeginCheckout;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class BeginCheckoutTest extends TestCase
{
    private const COUPON_CODE = 'GTMTEST';

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/include_coupon 1
     */
    public function testIncludesCouponFromCheckoutSessionWhenEnabled(): void
    {
        $ecommerce = $this->getEcommerceData();

        $this->assertSame(self::COUPON_CODE, $ecommerce['coupon']);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/include_coupon 0
     */
    public function testOmitsCouponWhenDisabled(): void
    {
        $ecommerce = $this->getEcommerceData();

        $this->assertArrayNotHasKey('coupon', $ecommerce);
    }

    private function getEcommerceData(): array
    {
        $objectManager = ObjectManager::getInstance();
        $quote = $objectManager->get(QuoteFactory::class)->create();
        $quote->setCouponCode(self::COUPON_CODE);
        $objectManager->get(CheckoutSession::class)->replaceQuote($quote);

        $data = $objectManager->create(BeginCheckout::class)->get();

        $this->assertSame('begin_checkout', $data['event']);
        $this->assertArrayHasKey('ecommerce', $data);

        return $data['ecommerce'];
    }
}
