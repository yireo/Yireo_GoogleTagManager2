<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Context;

use Magento\Customer\Model\Session as CustomerSession;
use Yireo\GoogleTagManager2\ServerSide\Consent\ConsentResolverInterface;
use Yireo\GoogleTagManager2\ServerSide\Config;

class ContextCollector
{
    private CookieReader $cookieReader;
    private CustomerSession $customerSession;
    private Config $config;
    private ConsentResolverInterface $consentResolver;

    public function __construct(
        CookieReader $cookieReader,
        CustomerSession $customerSession,
        Config $config,
        ConsentResolverInterface $consentResolver
    ) {
        $this->cookieReader = $cookieReader;
        $this->customerSession = $customerSession;
        $this->config = $config;
        $this->consentResolver = $consentResolver;
    }

    public function collect(?int $storeId = null): Context
    {
        $customerId = (int)$this->customerSession->getCustomerId();

        return new Context(
            $this->cookieReader->getClientId() ?? $this->generateClientId(),
            $this->cookieReader->getSessionId($this->config->getMeasurementId($storeId)),
            $customerId > 0 ? (string)$customerId : null,
            $this->consentResolver->getConsent($storeId)
        );
    }

    public function createFallback(?string $userId = null): Context
    {
        return new Context($this->generateClientId(), null, $userId, null);
    }

    public function generateClientId(): string
    {
        return random_int(1000000000, 2147483647) . '.' . time();
    }
}
