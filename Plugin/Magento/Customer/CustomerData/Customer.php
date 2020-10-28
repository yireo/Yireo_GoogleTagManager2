<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Plugin\Magento\Customer\CustomerData;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\CustomerData\Customer as CustomerData;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Customer
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * Customer constructor.
     * @param CustomerSession $customerSession
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        CustomerSession $customerSession,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param CustomerData $subject
     * @param $result
     *
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetSectionData(CustomerData $subject, $result)
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
