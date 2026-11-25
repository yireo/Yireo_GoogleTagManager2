<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\SignUp as SignUpEvent;
use Psr\Log\LoggerInterface;
use Throwable;

class TriggerSignUpDataLayerEvent implements ObserverInterface
{
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;
    private SignUpEvent $signUpEvent;
    private LoggerInterface $logger;

    public function __construct(
        CustomerSessionDataProviderInterface $customerSessionDataProvider,
        SignUpEvent $signUpEvent,
        LoggerInterface $logger
    ) {
        $this->customerSessionDataProvider = $customerSessionDataProvider;
        $this->signUpEvent = $signUpEvent;
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
        $eventData = $this->signUpEvent->get();
        $this->customerSessionDataProvider->add('sign_up_event', $eventData);
    }
}
