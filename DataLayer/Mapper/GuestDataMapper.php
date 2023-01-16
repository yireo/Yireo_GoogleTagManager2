<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\Attribute\GetAttributeValue;
use Yireo\GoogleTagManager2\Util\CamelCase;

class GuestDataMapper
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
     * @param OrderInterface $order
     * @return array
     * @throws LocalizedException
     */
    public function mapByOrder(OrderInterface $order): array
    {
        $prefix = 'customer_';
        $guestData = [];
        $guestFields = $this->getGuestFields();
        foreach ($guestFields as $guestAttributeCode) {
            $guestAttributeCode = $prefix . $guestAttributeCode;
            $dataLayerKey = $prefix . $guestAttributeCode;
            $attributeValue = $this->getAttributeValue->getAttributeValue($order, 'order', $guestAttributeCode);
            if (empty($attributeValue)) {
                continue;
            }

            $guestData[$dataLayerKey] = $attributeValue;
        }

        return $guestData;
    }

    /**
     * @return string[]
     */
    private function getGuestFields(): array
    {
        return array_filter(array_merge(['id'], $this->config->getCustomerEavAttributeCodes()));
    }
}
