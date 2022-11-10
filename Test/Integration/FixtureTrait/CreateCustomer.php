<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\ObjectManager;

trait CreateCustomer
{
    public function createCustomer(
        int $id = 1,
        array $data = []
    ) {
        $objectManager = ObjectManager::getInstance();
        $customer = $objectManager->create(Customer::class);
        $customer->setWebsiteId(1)
            ->setId($id)
            ->setEmail('customer@example.com')
            ->setPassword('password')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setIsActive(1)
            ->setPrefix('Mr.')
            ->setFirstname('John')
            ->setMiddlename('A')
            ->setLastname('Smith')
            ->setSuffix('Esq.')
            ->setDefaultBilling(1)
            ->setDefaultShipping(1)
            ->setTaxvat('12')
            ->setGender(0)
            ->addData($data);

        $customer->isObjectNew(true);
        $customer->save();

        $customerRegistry = $objectManager->get(CustomerRegistry::class);
        $customerRegistry->remove($customer->getId());
    }
}
