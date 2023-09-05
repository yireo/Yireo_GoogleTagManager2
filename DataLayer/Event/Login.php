<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CustomerDataMapper;

class Login implements EventInterface
{
    private CustomerInterface $customer;
    private CustomerDataMapper $customerDataMapper;

    public function __construct(
        CustomerDataMapper $customerDataMapper
    ) {
        $this->customerDataMapper = $customerDataMapper;
    }

    public function setCustomer(CustomerInterface $customer): Login
    {
        $this->customer = $customer;
        return $this;
    }

    public function get(): array
    {
        return [
            'event' => 'login',
            'customer' => $this->customerDataMapper->mapByCustomer($this->customer)
        ];
    }
}
