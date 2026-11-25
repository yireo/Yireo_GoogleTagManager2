<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\Login as LoginEvent;
use Psr\Log\LoggerInterface;
use Throwable;

class TriggerLoginDataLayerEvent implements ObserverInterface
{
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;
    private LoginEvent $loginEvent;
    private LoggerInterface $logger;

    public function __construct(
        CustomerSessionDataProviderInterface $customerSessionDataProvider,
        LoginEvent $loginEvent,
        LoggerInterface $logger
    ) {
        $this->customerSessionDataProvider = $customerSessionDataProvider;
        $this->loginEvent = $loginEvent;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $this->doExecute($observer);
        } catch (Throwable $exception) {
            $this->logger->error(
                'GTM data layer observer ' . self::class . ' failed: ' . $exception->getMessage()
            );
        }
    }

    private function doExecute(Observer $observer): void
    {
        $customer = $observer->getEvent()->getCustomer();
        $eventData = $this->loginEvent->setCustomer($customer)->get();
        $this->customerSessionDataProvider->add('login_event', $eventData);
    }
}
