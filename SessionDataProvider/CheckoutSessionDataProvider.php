<?php
declare(strict_types=1);

namespace Tagging\GTM\SessionDataProvider;

use Magento\Checkout\Model\Session as CheckoutSession;
use Tagging\GTM\Api\CheckoutSessionDataProviderInterface;

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
        $gtmData = $this->get();
        $gtmData[$identifier] = $data;
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
