<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Context\ContextCollector;
use Yireo\GoogleTagManager2\ServerSide\EventPublisher;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\ServerSideConfig;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class CookieRestrictionConsentTest extends TestCase
{
    use ServerSideConfig;

    private const EVENT = ['event' => 'add_to_cart', 'ecommerce' => ['currency' => 'USD', 'value' => 10]];

    protected function tearDown(): void
    {
        unset($_COOKIE['user_allowed_save_cookie']);
    }

    public function testEventIsSkippedWithoutCookieRestrictionCookie(): void
    {
        $this->configure(true);
        unset($_COOKIE['user_allowed_save_cookie']);

        $this->assertNull($this->publish('add_to_cart:no-cookie'));
        $this->assertSame([], $this->getOutboxRows('add_to_cart'));
    }

    public function testEventIsSkippedWhenCookieRestrictionCookieIsForOtherWebsite(): void
    {
        $this->configure(true);
        $_COOKIE['user_allowed_save_cookie'] = json_encode([(string)($this->getWebsiteId() + 1) => 1]);

        $this->assertNull($this->publish('add_to_cart:other-website'));
        $this->assertSame([], $this->getOutboxRows('add_to_cart'));
    }

    public function testEventIsPublishedWithCookieRestrictionCookie(): void
    {
        $this->configure(true);
        $_COOKIE['user_allowed_save_cookie'] = json_encode([(string)$this->getWebsiteId() => 1]);

        $this->assertNotNull($this->publish('add_to_cart:cookie'));
        $this->assertCount(1, $this->getOutboxRows('add_to_cart'));
    }

    public function testEventIsPublishedWithoutCookieRestrictionMode(): void
    {
        $this->configure(false);
        unset($_COOKIE['user_allowed_save_cookie']);

        $this->assertNotNull($this->publish('add_to_cart:no-restriction'));
        $this->assertCount(1, $this->getOutboxRows('add_to_cart'));
    }

    private function configure(bool $cookieRestriction): void
    {
        $this->configureServerSide(['serverside_tracking/require_consent' => '1', 'consent/enabled' => '0']);
        $scopeConfig = ObjectManager::getInstance()->get(MutableScopeConfigInterface::class);
        $value = $cookieRestriction ? '1' : '0';
        $scopeConfig->setValue('web/cookie/cookie_restriction', $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $scopeConfig->setValue('web/cookie/cookie_restriction', $value, ScopeInterface::SCOPE_STORE, 'default');
        $this->clearOutbox();
    }

    private function publish(string $dedupKey): ?int
    {
        $objectManager = ObjectManager::getInstance();
        $context = $objectManager->get(ContextCollector::class)->collect(1);

        return $objectManager->get(EventPublisher::class)->publish(self::EVENT, 1, $dedupKey, $context);
    }

    private function getWebsiteId(): int
    {
        return (int)ObjectManager::getInstance()->get(StoreManagerInterface::class)->getStore(1)->getWebsiteId();
    }
}
