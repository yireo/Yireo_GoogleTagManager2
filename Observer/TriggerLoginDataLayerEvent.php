<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\Login as LoginEvent;

class TriggerLoginDataLayerEvent implements ObserverInterface
{
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;
    private LoginEvent $loginEvent;

    public function __construct(
        CustomerSessionDataProviderInterface $customerSessionDataProvider,
        LoginEvent $loginEvent
    ) {
        $this->customerSessionDataProvider = $customerSessionDataProvider;
        $this->loginEvent = $loginEvent;
    }

    public function execute(Observer $observer)
    {
        $this->customerSessionDataProvider->add('login_event', $this->loginEvent->get());
    }
}
