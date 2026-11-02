<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Stub;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class NullCodeGroupRepositoryStub implements GroupRepositoryInterface
{
    private GroupRepositoryInterface $groupRepository;
    private GroupInterfaceFactory $groupFactory;

    public function __construct(
        GroupRepositoryInterface $groupRepository,
        GroupInterfaceFactory $groupFactory
    ) {
        $this->groupRepository = $groupRepository;
        $this->groupFactory = $groupFactory;
    }

    public function save(GroupInterface $group)
    {
        return $this->groupRepository->save($group);
    }

    public function getById($id)
    {
        $originalGroup = $this->groupRepository->getById($id);

        /** @var GroupInterface $group */
        $group = $this->groupFactory->create();
        $group->setId($originalGroup->getId());
        $group->setTaxClassId($originalGroup->getTaxClassId());
        $group->setCode(null);

        return $group;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        return $this->groupRepository->getList($searchCriteria);
    }

    public function delete(GroupInterface $group)
    {
        return $this->groupRepository->delete($group);
    }

    public function deleteById($id)
    {
        return $this->groupRepository->deleteById($id);
    }
}
