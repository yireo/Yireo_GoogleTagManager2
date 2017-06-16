<?php

namespace Yireo\GoogleTagManager2\Plugin\Magento\Customer\CustomerData;

class Customer
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * Customer constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
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
