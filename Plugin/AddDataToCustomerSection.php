<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Tagging\GTM\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\CustomerData\Customer as CustomerData;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Tagging\GTM\Api\CustomerSessionDataProviderInterface;
use Tagging\GTM\DataLayer\Mapper\CustomerDataMapper;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Psr\Log\LoggerInterface;
use Tagging\GTM\Logger\Debugger;
use Tagging\GTM\Config\Config;

class AddDataToCustomerSection
{
    private CustomerSession $customerSession;
    private GroupRepositoryInterface $groupRepository;
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;
    private CustomerDataMapper $customerDataMapper;
    private CustomerRepositoryInterface $customerRepository;
    private CollectionFactory $orderCollectionFactory;
    private LoggerInterface $logger;
    private Debugger $debugger;
    private Config $config;

    /**
     * Customer constructor.
     * @param CustomerSession $customerSession
     * @param GroupRepositoryInterface $groupRepository
     * @param CustomerSessionDataProviderInterface $customerSessionDataProvider
     * @param CustomerDataMapper $customerDataMapper
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerSession $customerSession,
        GroupRepositoryInterface $groupRepository,
        CustomerSessionDataProviderInterface $customerSessionDataProvider,
        CustomerDataMapper $customerDataMapper,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $orderCollectionFactory,
        LoggerInterface $logger,
        Debugger $debugger
        Config $config
    ) {
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->customerSessionDataProvider = $customerSessionDataProvider;
        $this->customerDataMapper = $customerDataMapper;
        $this->customerRepository = $customerRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger = $logger;
        $this->debugger = $debugger;
    }

    /**
     * @param CustomerData $subject
     * @param mixed $result
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetSectionData(CustomerData $subject, $result)
    {
        if (!is_array($result)) {
            return $result;
        }


        $gtmData = $this->getGtmData();
        $gtmOnce = $this->customerSessionDataProvider->get();
        $this->customerSessionDataProvider->clear();

        return array_merge($result, ['gtm' => $gtmData, 'gtm_events' => $gtmOnce]);
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getGtmData(): array
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [
                'customerLoggedIn' => 0,
                'customerId' => 0,
                'customerGroupId' => 0,
                'customerGroupCode' => 'GUEST',
                'visitorLoginState' => 'Logged out',
                'visitorLifeTimeValue' => 0,
                'visitorExistingCustomer' => 'No',
                'visitorType' => 'NOT LOGGED IN'
            ];
        }

        $customerId = $this->customerSession->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);
        $customerGtmData = $this->customerDataMapper->mapByCustomer($customer);
        $customerGroup = $this->groupRepository->getById($this->customerSession->getCustomerGroupId());
        $totalLifeTimeValue = $this->getLifeTimeValue($customer->getEmail());

        return array_merge([
            'customerLoggedIn' => 1,
            'customerId' => $customerId,
            'customerGroupId' => $customerGroup->getId(),
            'customerGroupCode' => strtoupper($customerGroup->getCode()),
            'visitorLoginState' => 'logged in',
            'visitorLifeTimeValue' => $totalLifeTimeValue,
            'visitorExistingCustomer' => $totalLifeTimeValue > 0 ? 'Yes' : 'No',
            'visitorType' => 'LOGGED IN'
        ], $customerGtmData);
    }

    private function getLifeTimeValue($customerEmail) 
    {
        $this->debugger->debug("Calculating lifetime value for customer email: " . $customerEmail);

        if(!$this->config->isLifetimeValueEnabled()) {
            return 0;
        }

        try {
            $collection = $this->orderCollectionFactory->create();
            $collection->addAttributeToFilter('customer_email', $customerEmail); 
            $collection->addAttributeToSelect('grand_total');
            
            $lifetimeValue = 0.0;
            foreach ($collection as $order) {
                $lifetimeValue += (float)$order->getGrandTotal();
            }

            $this->debugger->debug("Calculated lifetime value: " . $lifetimeValue);

            return $lifetimeValue;
        } catch (\Exception $e) {
            $this->logger->error("Error calculating lifetime value: " . $e->getMessage());
            $this->debugger->debug("Error calculating lifetime value: " . $e->getMessage());
            return 0.0; // Return a default value in case of an error
        }
    }
}