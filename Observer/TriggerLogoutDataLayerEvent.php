<?php declare(strict_types=1);

namespace AdPage\GTM\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use AdPage\GTM\Api\CustomerSessionDataProviderInterface;
use AdPage\GTM\DataLayer\Event\Logout as LogoutEvent;

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
        $this->customerSessionDataProvider->add('logout_event', $this->logoutEvent->get());
    }
}
