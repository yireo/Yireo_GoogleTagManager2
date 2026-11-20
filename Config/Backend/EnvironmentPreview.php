<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config\Backend;

use Magento\Framework\App\Config\Value;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Exception\InvalidConfig;

class EnvironmentPreview extends Value
{
    public function beforeSave()
    {
        $value = trim((string)$this->getValue(), " \n\r\t\v\0");
        $this->setValue($value);

        if ($value !== '' && false === (bool)preg_match(Config::ENVIRONMENT_PREVIEW_PATTERN, $value)) {
            throw new InvalidConfig('Invalid GTM environment preview. It should look like "env-6"');
        }

        return parent::beforeSave();
    }
}
