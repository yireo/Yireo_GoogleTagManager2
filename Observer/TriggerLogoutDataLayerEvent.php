<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\Logout as LogoutEvent;
use Psr\Log\LoggerInterface;
use Throwable;

class TriggerLogoutDataLayerEvent implements ObserverInterface
{
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;
    private LogoutEvent $logoutEvent;
    private LoggerInterface $logger;

    public function __construct(
        CustomerSessionDataProviderInterface $customerSessionDataProvider,
        LogoutEvent $logoutEvent,
        LoggerInterface $logger
    ) {
        $this->customerSessionDataProvider = $customerSessionDataProvider;
        $this->logoutEvent = $logoutEvent;
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
        $this->customerSessionDataProvider->add('logout_event', $this->logoutEvent->get());
    }
}
