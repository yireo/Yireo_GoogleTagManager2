<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Customer\Api\Data\CustomerInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\Attribute\GetAttributeValue;
use Yireo\GoogleTagManager2\Util\CamelCase;

class CustomerDataMapper
{
    private CamelCase $camelCase;
    private Config $config;
    private GetAttributeValue $getAttributeValue;

    /**
     * @param CamelCase $camelCase
     * @param Config $config
     * @param GetAttributeValue $getAttributeValue
     */
    public function __construct(
        CamelCase $camelCase,
        Config $config,
        GetAttributeValue $getAttributeValue
    ) {
        $this->camelCase = $camelCase;
        $this->config = $config;
        $this->getAttributeValue = $getAttributeValue;
    }

    /**
     * @param CustomerInterface $customer
     * @param string $prefix
     * @return array
     */
    public function mapByCustomer(CustomerInterface $customer, string $prefix = ''): array
    {
        $customerData = [];
        $customerFields = $this->getCustomerFields();
        foreach ($customerFields as $customerAttributeCode) {
            $dataLayerKey = lcfirst($prefix . $this->camelCase->to($customerAttributeCode));
            $attributeValue = $this->getAttributeValue->getCustomerAttributeValue($customer, $customerAttributeCode);
            if (empty($attributeValue)) {
                continue;
            }

            $customerData[$dataLayerKey] = $attributeValue;
        }

        return $customerData;
    }

    /**
     * @return string[]
     */
    private function getCustomerFields(): array
    {
        return array_filter(array_merge(['id'], $this->config->getCustomerEavAttributeCodes()));
    }
}
