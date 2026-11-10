<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Context;

use Magento\Customer\Model\Session as CustomerSession;
use Yireo\GoogleTagManager2\ServerSide\Config;

class ContextCollector
{
    private CookieReader $cookieReader;
    private CustomerSession $customerSession;
    private Config $config;

    public function __construct(
        CookieReader $cookieReader,
        CustomerSession $customerSession,
        Config $config
    ) {
        $this->cookieReader = $cookieReader;
        $this->customerSession = $customerSession;
        $this->config = $config;
    }

    public function collect(?int $storeId = null): Context
    {
        $customerId = (int)$this->customerSession->getCustomerId();

        return new Context(
            $this->cookieReader->getClientId() ?? $this->generateClientId(),
            $this->cookieReader->getSessionId($this->config->getMeasurementId($storeId)),
            $customerId > 0 ? (string)$customerId : null,
            $this->cookieReader->getConsent()
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
