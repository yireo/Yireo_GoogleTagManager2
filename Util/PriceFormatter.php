<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

/**
 * Class PriceFormatter
 *
 * @package Yireo\GoogleTagManager2\Util
 */
class PriceFormatter
{
    /**
     * @param float $price
     * @return string
     */
    public function format(float $price): string
    {
        return number_format($price, 2, ".", "");
    }
}
