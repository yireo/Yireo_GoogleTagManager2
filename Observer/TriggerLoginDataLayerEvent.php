<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;

class TriggerLoginDataLayerEvent implements ObserverInterface
{
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;

    public function __construct(
        CustomerSessionDataProviderInterface $customerSessionDataProvider,
    ) {
        $this->customerSessionDataProvider = $customerSessionDataProvider;
    }

    public function execute(Observer $observer)
    {
        $this->customerSessionDataProvider->append([
            'event' => 'login'
        ]);
    }
}
