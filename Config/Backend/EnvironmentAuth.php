<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config\Backend;

use Magento\Framework\App\Config\Value;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Exception\InvalidConfig;

class EnvironmentAuth extends Value
{
    public function beforeSave()
    {
        $value = trim((string)$this->getValue(), " \n\r\t\v\0");
        $this->setValue($value);

        if ($value !== '' && false === (bool)preg_match(Config::ENVIRONMENT_AUTH_PATTERN, $value)) {
            throw new InvalidConfig('Invalid GTM environment auth. It should only contain letters, digits, "-" and "_"');
        }

        return parent::beforeSave();
    }
}
