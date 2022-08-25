<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Customer\Api\Data\CustomerInterface;
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
        /** @var CustomerInterface $customerModel */
        $customerModel = $observer->getData('model');

        $this->customerSessionDataProvider->append([
           'event' => 'login',
            'customer' => [
                'id' => $customerModel->getId(),
                'email' => $customerModel->getEmail(),
            ]
        ]);
    }
}
