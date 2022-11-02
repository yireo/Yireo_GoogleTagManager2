<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\Logout as LogoutEvent;

class TriggerLogoutDataLayerEvent implements ObserverInterface
{
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;
    private LogoutEvent $logoutEvent;

    public function __construct(
        CustomerSessionDataProviderInterface $customerSessionDataProvider,
        LogoutEvent $logoutEvent
    ) {
        $this->customerSessionDataProvider = $customerSessionDataProvider;
        $this->logoutEvent = $logoutEvent;
    }

    public function execute(Observer $observer)
    {
        $this->customerSessionDataProvider->append($this->logoutEvent->get());
    }
}
