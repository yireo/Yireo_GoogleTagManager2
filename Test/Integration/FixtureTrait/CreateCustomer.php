<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

trait CreateCustomer
{
    public function createCustomer(
        int $id = 1,
        array $data = []
    ): CustomerInterface {
        $objectManager = ObjectManager::getInstance();
        $customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        try {
            return $customerRepository->getById($id);
        } catch (NoSuchEntityException $e) {
        } catch (LocalizedException $e) {
        }

        /** @var Customer $customer */
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
        // @phpstan-ignore-next-line
        $customer->save();

        $customerRegistry = $objectManager->get(CustomerRegistry::class);
        $customerRegistry->remove($customer->getId());
        return $customerRepository->getById($customer->getId());
    }
}
