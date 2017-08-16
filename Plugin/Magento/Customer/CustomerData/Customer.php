<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Plugin\Magento\Customer\CustomerData;

/**
 * Class Customer
 *
 * @package Yireo\GoogleTagManager2\Plugin\Magento\Customer\CustomerData
 */
class Customer
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * Customer constructor.
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param $result
     * @return mixed
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        if (empty($result)) {
            return $result;
        }

        $customerGroup = $this->groupRepository->getById($this->customerSession->getCustomerGroupId());

        $result['id'] = $this->customerSession->getCustomerId();
        $result['group_id'] = $customerGroup->getId();
        $result['group_code'] = $customerGroup->getCode();
        return $result;
    }
}
