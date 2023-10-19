<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author    Jisse Reitsma <jisse@yireo.com>
 * @copyright 2022 Yireo (https://www.yireo.com/)
 * @license   Open Source License (OSL v3)
 */

namespace AdPage\GTM\Util;

class CamelCase
{
    /**
     * @param string $string
     * @return string
     */
    public function from(string $string): string
    {
        return strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $string), '_'));
    }

    /**
     * @param string $string
     * @return string
     */
    public function to(string $string): string
    {
        return str_replace('_', '', ucwords($string, '_'));
    }
}
