<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\SessionDataProvider;

use Magento\Customer\Model\Session as CustomerSession;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\Logger\Debugger;

class CustomerSessionDataProvider implements CustomerSessionDataProviderInterface
{
    private CustomerSession $customerSession;
    private Debugger $debugger;

    public function __construct(
        CustomerSession $customerSession,
        Debugger $debugger
    ) {
        $this->customerSession = $customerSession;
        $this->debugger = $debugger;
    }

    public function add(string $identifier, array $data)
    {
        $gtmData = $this->get();
        $gtmData[$identifier] = $data;
        $this->debugger->debug('CustomerSessionDataProvider::add(): ' . $identifier, $data);
        $this->customerSession->setYireoGtmData($gtmData);
    }

    public function get(): array
    {
        $gtmData = $this->customerSession->getYireoGtmData();
        if (is_array($gtmData)) {
            return $gtmData;
        }

        return [];
    }

    public function clear()
    {
        $this->customerSession->setYireoGtmData([]);
    }
}
