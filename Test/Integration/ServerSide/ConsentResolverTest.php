<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Consent\ConsentResolverInterface;
use Yireo\GoogleTagManager2\ServerSide\Consent\CookieConsentResolver;
use Yireo\GoogleTagManager2\ServerSide\Context\ContextCollector;
use Yireo\GoogleTagManager2\ServerSide\EventPublisher;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\ServerSideConfig;
use Yireo\GoogleTagManager2\Test\Integration\Stub\ConsentResolverStub;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class ConsentResolverTest extends TestCase
{
    use ServerSideConfig;

    public function testDefaultResolverIsCookieResolver(): void
    {
        $this->assertInstanceOf(
            CookieConsentResolver::class,
            ObjectManager::getInstance()->get(ConsentResolverInterface::class)
        );
    }

    public function testCustomResolverIsUsedForEnforcementAndContext(): void
    {
        $this->configureServerSide(['serverside_tracking/require_consent' => '1', 'consent/enabled' => '0']);
        $this->clearOutbox();

        $resolver = new ConsentResolverStub();
        $objectManager = ObjectManager::getInstance();
        $config = $objectManager->create(Config::class, ['consentResolver' => $resolver]);
        $contextCollector = $objectManager->create(ContextCollector::class, [
            'consentResolver' => $resolver,
            'config' => $config,
        ]);
        $eventPublisher = $objectManager->create(EventPublisher::class, ['config' => $config]);

        $this->assertTrue($config->requireConsent(1));

        $event = ['event' => 'add_to_cart', 'ecommerce' => ['currency' => 'USD', 'value' => 10]];
        $this->assertNull($eventPublisher->publish($event, 1, 'add_to_cart:a', $contextCollector->collect(1)));

        $resolver->consent = ['analytics' => true, 'marketing' => false, 'preferences' => false];
        $context = $contextCollector->collect(1);
        $this->assertTrue($context->hasConsent('analytics'));
        $this->assertNotNull($eventPublisher->publish($event, 1, 'add_to_cart:b', $context));

        $payload = json_decode($this->getOutboxRows('add_to_cart')[0]['payload'], true);
        $this->assertSame('DENIED', $payload['consent']['ad_user_data']);

        $resolver->active = false;
        $this->assertFalse($config->requireConsent(1));
    }
}
