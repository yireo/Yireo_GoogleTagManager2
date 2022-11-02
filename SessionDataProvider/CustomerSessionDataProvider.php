<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\SessionDataProvider;

use Magento\Customer\Model\Session as CustomerSession;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;

class CustomerSessionDataProvider implements CustomerSessionDataProviderInterface
{
    private CustomerSession $customerSession;

    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    public function add(string $identifier, array $data)
    {
        $gtmEvents = $this->get();
        $gtmEvents[$identifier] = $data;
        $this->customerSession->setYireoGtmEvents($gtmEvents);
    }

    public function get(): array
    {
        $gtmEvents = $this->customerSession->getYireoGtmEvents();
        if (is_array($gtmEvents)) {
            return $gtmEvents;
        }

        return [];
    }

    public function clear()
    {
        $this->customerSession->setYireoGtmEvents([]);
    }
}
