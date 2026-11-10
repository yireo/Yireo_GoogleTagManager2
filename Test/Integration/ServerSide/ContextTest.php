<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Context\Context;
use Yireo\GoogleTagManager2\ServerSide\Context\ContextRepository;
use Yireo\GoogleTagManager2\ServerSide\OrderEventPublisher;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetOrder;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\ServerSideConfig;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class ContextTest extends TestCase
{
    use GetOrder;
    use ServerSideConfig;

    public function testSaveAndLoad(): void
    {
        $repository = ObjectManager::getInstance()->get(ContextRepository::class);
        $repository->save(999001, null, new Context('1.2', '3', '4', ['analytics' => true]));
        $repository->save(999001, 999002, new Context('5.6', null, null, null));

        $this->assertNull($repository->getByOrderId(1));
        $context = $repository->getByOrderId(999002);
        $this->assertInstanceOf(Context::class, $context);
        $this->assertSame('5.6', $context->getClientId());
        $this->assertNull($context->getConsent());

        $this->assertSame('5.6', $repository->getByQuoteId(999001)->getClientId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testStoreContextFromCookies(): void
    {
        $this->configureServerSide();
        $order = $this->getOrder();
        $order->setQuoteId(999003);

        $_COOKIE['_ga'] = 'GA1.1.2222222222.1699999999';
        $_COOKIE['yireo_gtm2_consent'] = rawurlencode('{"analytics":true}');
        ObjectManager::getInstance()->get(OrderEventPublisher::class)->storeContext($order);
        unset($_COOKIE['_ga'], $_COOKIE['yireo_gtm2_consent']);

        $context = ObjectManager::getInstance()->get(ContextRepository::class)->getByOrderId((int)$order->getId());
        $this->assertSame('2222222222.1699999999', $context->getClientId());
        $this->assertTrue($context->hasConsent('analytics'));
        $this->assertFalse($context->hasConsent('marketing'));
    }
}
