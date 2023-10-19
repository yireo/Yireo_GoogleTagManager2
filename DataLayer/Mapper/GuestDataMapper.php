<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Mapper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use AdPage\GTM\Config\Config;
use AdPage\GTM\Util\Attribute\GetAttributeValue;

class GuestDataMapper
{
    private Config $config;
    private GetAttributeValue $getAttributeValue;

    /**
     * @param Config $config
     * @param GetAttributeValue $getAttributeValue
     */
    public function __construct(
        Config $config,
        GetAttributeValue $getAttributeValue
    ) {
        $this->config = $config;
        $this->getAttributeValue = $getAttributeValue;
    }

    /**
     * @param Order $order
     * @return array
     * @throws LocalizedException
     */
    public function mapByOrder(Order $order): array
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
