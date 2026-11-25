<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Config\Source\CustomerAttributes as CustomerAttributesSource;
use Yireo\GoogleTagManager2\Exception\InvalidConfig;

class CustomerAttributes extends Value
{
    private const TRIM_CHARACTERS = " \f\n\r\t\v\x00";

    private CustomerAttributesSource $customerAttributesSource;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        CustomerAttributesSource $customerAttributesSource,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->customerAttributesSource = $customerAttributesSource;
    }

    public function beforeSave()
    {
        $value = $this->getValue();
        $codes = is_array($value) ? $value : explode(',', (string)$value);
        $allowedCodes = array_column($this->customerAttributesSource->toOptionArray(), 'value');

        foreach ($codes as $code) {
            $code = trim((string)$code, self::TRIM_CHARACTERS);
            if ($code === '') {
                continue;
            }

            if (in_array($code, Config::CUSTOMER_ATTRIBUTE_DENY_LIST, true)
                || false === in_array($code, $allowedCodes, true)) {
                throw new InvalidConfig(sprintf('Customer attribute "%s" is not allowed in the data layer', $code));
            }
        }

        return parent::beforeSave();
    }
}
