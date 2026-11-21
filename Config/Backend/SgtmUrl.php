<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config\Backend;

use Magento\Framework\App\Config\Value;
use Yireo\GoogleTagManager2\Exception\InvalidConfig;
use Yireo\GoogleTagManager2\ServerSide\Config;

class SgtmUrl extends Value
{
    public function beforeSave()
    {
        $value = trim((string)$this->getValue(), " \n\r\t\v\0");
        $this->setValue($value);

        if ($value !== '' && false === Config::isSecureUrl($value)) {
            throw new InvalidConfig('Invalid server-side GTM URL. It should be a valid URL starting with "https://"');
        }

        return parent::beforeSave();
    }
}
