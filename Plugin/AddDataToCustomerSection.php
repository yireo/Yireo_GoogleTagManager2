<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\CustomerData\Customer as CustomerData;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CustomerDataMapper;
use Psr\Log\LoggerInterface;
use Throwable;

class AddDataToCustomerSection
{
    private CustomerSession $customerSession;
    private GroupRepositoryInterface $groupRepository;
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;
    private CustomerDataMapper $customerDataMapper;
    private CustomerRepositoryInterface $customerRepository;
    private LoggerInterface $logger;

    /**
     * Customer constructor.
     * @param CustomerSession $customerSession
     * @param GroupRepositoryInterface $groupRepository
     * @param CustomerSessionDataProviderInterface $customerSessionDataProvider
     * @param CustomerDataMapper $customerDataMapper
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerSession $customerSession,
        GroupRepositoryInterface $groupRepository,
        CustomerSessionDataProviderInterface $customerSessionDataProvider,
        CustomerDataMapper $customerDataMapper,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->customerSessionDataProvider = $customerSessionDataProvider;
        $this->customerDataMapper = $customerDataMapper;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * @param CustomerData $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGetSectionData(CustomerData $subject, $result)
    {
        if (!is_array($result)) {
            return $result;
        }


        try {
            $gtmData = $this->getGtmData();
            $gtmOnce = $this->customerSessionDataProvider->get();
            $this->customerSessionDataProvider->clear();
        } catch (Throwable $exception) {
            $this->logger->error('GTM data layer plugin ' . self::class . ' failed: ' . $exception->getMessage());
            return $result;
        }

        return array_merge_recursive($result, ['gtm' => $gtmData, 'gtm_events' => $gtmOnce]);
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
                'customerGroupCode' => 'GUEST'
            ];
        }

        $customerId = $this->customerSession->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);
        $customerGtmData = $this->customerDataMapper->mapByCustomer($customer);
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $customerGroupCode = '';

        try {
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupId = $customerGroup->getId();
            $customerGroupCode = strtoupper((string)$customerGroup->getCode());
        } catch (NoSuchEntityException $e) {
        }

        return array_merge([
            'customerLoggedIn' => 1,
            'customerId' => $customerId,
            'customerGroupId' => $customerGroupId,
            'customerGroupCode' => $customerGroupCode
        ], $customerGtmData);
    }
}
