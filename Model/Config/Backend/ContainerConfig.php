<?php declare(strict_types=1);

namespace Tagging\GTM\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Tagging\GTM\Exception\InvalidConfig;

class ContainerConfig extends Value
{
    public function beforeSave()
    {
        if (false === $this->validate()) {
            throw new InvalidConfig('Invalid container HEAD code');
        }

        return parent::beforeSave();
    }

    private function validate(): bool
    {
        if (!empty($this->getValue())) {
            return true;
        }

        return false;
    }
}
