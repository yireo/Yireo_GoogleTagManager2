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

    public function set(string $name, $value)
    {
        $gtmData = $this->get();
        $gtmData[$name] = $value;
        $this->checkoutSession->setYireoGtmData($gtmData);
    }

    public function append(array $data)
    {
        $gtmData = $this->get();
        $gtmData = array_merge($gtmData, $data);
        $this->checkoutSession->setYireoGtmData($gtmData);
    }

    public function get(): array
    {
        $gtmData = $this->checkoutSession->getYireoGtmData();
        if (is_array($gtmData)) {
            return $gtmData;
        }

        return [];
    }

    public function clear()
    {
        $this->checkoutSession->setYireoGtmData([]);
    }
}
