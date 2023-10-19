<?php declare(strict_types=1);

namespace AdPage\GTM\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\State;
use AdPage\GTM\Exception\InvalidConfig;

class ContainerId extends Value
{
    public function beforeSave()
    {
        if (false === $this->validate()) {
            throw new InvalidConfig('Invalid container ID "' . $this->getValue() . '". It should start with "GTM-"');
        }

        return parent::beforeSave();
    }

    private function validate(): bool
    {
        if ($this->_appState->getMode() === State::MODE_DEVELOPER) {
            return true;
        }

        if (empty($this->getValue())) {
            return true;
        }

        if (preg_match('/^GTM-/', $this->getValue())) {
            return true;
        }

        return false;
    }
}
