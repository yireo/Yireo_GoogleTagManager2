<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Customer\Api\Data\CustomerInterface;
use Yireo\GoogleTagManager2\Api\Data\CustomerTagInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\Attribute\GetAttributeValue;
use Yireo\GoogleTagManager2\Util\CamelCase;

class CustomerDataMapper
{
    private CamelCase $camelCase;
    private Config $config;
    private GetAttributeValue $getAttributeValue;
    private array $dataLayerMapping;

    /**
     * @param CamelCase         $camelCase
     * @param Config            $config
     * @param GetAttributeValue $getAttributeValue
     * @param array             $dataLayerMapping
     */
    public function __construct(
        CamelCase $camelCase,
        Config $config,
        GetAttributeValue $getAttributeValue,
        array $dataLayerMapping = []
    ) {
        $this->camelCase = $camelCase;
        $this->config = $config;
        $this->getAttributeValue = $getAttributeValue;
        $this->dataLayerMapping = $dataLayerMapping;
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

        $customerData = $this->parseDataLayerMapping($customer, $customerData);

        return $customerData;
    }

    /**
     * @return string[]
     */
    private function getCustomerFields(): array
    {
        return array_filter(array_merge(['id'], $this->config->getCustomerEavAttributeCodes()));
    }

    /**
     * @param CustomerInterface $customer
     * @param array             $data
     * @return array
     */
    private function parseDataLayerMapping(CustomerInterface $customer, array $data): array
    {
        if (empty($this->dataLayerMapping)) {
            return [];
        }

        foreach ($this->dataLayerMapping as $tagName => $tagValue) {
            if (is_string($tagValue) && array_key_exists($tagValue, $data)) {
                $data[$tagName] = $data[$tagValue];
                continue;
            }

            if ($tagValue instanceof CustomerTagInterface) {
                $tagValue->setCustomer($customer);
                $data[$tagName] = $tagValue->get();
                continue;
            }

            if ($tagValue instanceof TagInterface) {
                $data[$tagName] = $tagValue->get();
            }
        }

        return $data;
    }
}
