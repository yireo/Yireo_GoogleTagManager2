<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Api\Data;

use Magento\Customer\Api\Data\CustomerInterface;

interface CustomerTagInterface extends TagInterface
{
    /**
     * @return mixed
     */
    public function setCustomer(CustomerInterface $customer);
}
