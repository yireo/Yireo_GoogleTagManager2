<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Yireo\GoogleTagManager2\Config\SecureUrlValidator;
use Yireo\GoogleTagManager2\Exception\InvalidConfig;

class SgtmUrl extends Value
{
    private SecureUrlValidator $secureUrlValidator;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        SecureUrlValidator $secureUrlValidator,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->secureUrlValidator = $secureUrlValidator;
    }

    public function beforeSave()
    {
        $value = trim((string)$this->getValue(), " \f\n\r\t\v\x00");
        $this->setValue($value);

        if ($value !== '' && false === $this->secureUrlValidator->isValid($value)) {
            throw new InvalidConfig(
                'Invalid URL. It should be a public HTTPS URL with a domain name (for instance https://sgtm.example.com)'
            );
        }

        return parent::beforeSave();
    }
}
