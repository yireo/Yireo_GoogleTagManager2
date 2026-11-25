<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config\Backend;

use Magento\Framework\App\Config\Value;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Exception\InvalidConfig;

class ContainerId extends Value
{
    private const TRIM_CHARACTERS = " \f\n\r\t\v\x00";

    public function beforeSave()
    {
        $value = trim((string)$this->getValue(), self::TRIM_CHARACTERS);
        $this->setValue($value);

        if (false === $this->validate($value)) {
            throw new InvalidConfig(
                'Invalid container ID. Every comma-separated ID should look like "GTM-" plus uppercase letters and digits'
            );
        }

        return parent::beforeSave();
    }

    private function validate(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        foreach (explode(',', $value) as $id) {
            if (false === (bool)preg_match(Config::CONTAINER_ID_PATTERN, trim($id, self::TRIM_CHARACTERS))) {
                return false;
            }
        }

        return true;
    }
}
