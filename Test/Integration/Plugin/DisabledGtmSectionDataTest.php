<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Plugin;

use Magento\Checkout\CustomerData\Cart as CartCustomerData;
use Magento\Customer\CustomerData\Customer as CustomerCustomerData;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\CustomerData\GtmCheckout;
use Yireo\GoogleTagManager2\Plugin\AddDataToCartSection;
use Yireo\GoogleTagManager2\Plugin\AddDataToCustomerSection;
use Yireo\GoogleTagManager2\SessionDataProvider\CheckoutSessionDataProvider;

/**
 * @magentoAppArea frontend
 * @magentoConfigFixture current_store googletagmanager2/settings/enabled 0
 */
class DisabledGtmSectionDataTest extends TestCase
{
    public function testCartSectionStaysUnchangedAndPendingEventsAreDiscarded(): void
    {
        $sessionDataProvider = $this->createMock(CheckoutSessionDataProvider::class);
        $sessionDataProvider->expects($this->never())->method('get');
        $sessionDataProvider->expects($this->once())->method('clear');

        $plugin = ObjectManager::getInstance()->create(AddDataToCartSection::class, [
            'checkoutSessionDataProvider' => $sessionDataProvider,
        ]);

        $result = ['summary_count' => 1];
        $this->assertSame($result, $plugin->afterGetSectionData($this->createMock(CartCustomerData::class), $result));
    }

    public function testCustomerSectionStaysUnchangedAndPendingEventsAreDiscarded(): void
    {
        $sessionDataProvider = $this->createMock(CustomerSessionDataProviderInterface::class);
        $sessionDataProvider->expects($this->never())->method('get');
        $sessionDataProvider->expects($this->once())->method('clear');

        $plugin = ObjectManager::getInstance()->create(AddDataToCustomerSection::class, [
            'customerSessionDataProvider' => $sessionDataProvider,
        ]);

        $result = ['firstname' => 'John'];
        $subject = $this->createMock(CustomerCustomerData::class);
        $this->assertSame($result, $plugin->afterGetSectionData($subject, $result));
    }

    public function testCheckoutSectionIsEmptyAndPendingEventsAreDiscarded(): void
    {
        $sessionDataProvider = $this->createMock(CheckoutSessionDataProviderInterface::class);
        $sessionDataProvider->expects($this->never())->method('get');
        $sessionDataProvider->expects($this->once())->method('clear');

        $section = ObjectManager::getInstance()->create(GtmCheckout::class, [
            'checkoutSessionDataProvider' => $sessionDataProvider,
        ]);

        $this->assertSame([], $section->getSectionData());
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST123
     */
    public function testCheckoutSectionContainsPendingEventsWhenEnabled(): void
    {
        $sessionDataProvider = $this->createMock(CheckoutSessionDataProviderInterface::class);
        $sessionDataProvider->method('get')->willReturn(['add_to_cart_event' => ['event' => 'add_to_cart']]);
        $sessionDataProvider->expects($this->once())->method('clear');

        $section = ObjectManager::getInstance()->create(GtmCheckout::class, [
            'checkoutSessionDataProvider' => $sessionDataProvider,
        ]);

        $this->assertSame(
            ['gtm_events' => ['add_to_cart_event' => ['event' => 'add_to_cart']]],
            $section->getSectionData()
        );
    }
}
