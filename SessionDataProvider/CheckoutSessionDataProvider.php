<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\SessionDataProvider;

use Magento\Checkout\Model\Session as CheckoutSession;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;

class CheckoutSessionDataProvider implements CheckoutSessionDataProviderInterface
{
    private CheckoutSession $checkoutSession;

    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function add(string $identifier, array $data)
    {
        $gtmEvents = $this->get();
        $gtmEvents[$identifier] = $data;
        $this->checkoutSession->setYireoGtmEvents($gtmEvents);
    }

    public function get(): array
    {
        $gtmEvents = $this->checkoutSession->getYireoGtmEvents();
        if (is_array($gtmEvents)) {
            return $gtmEvents;
        }

        return [];
    }

    public function clear()
    {
        $this->checkoutSession->setYireoGtmEvents([]);
    }
}
