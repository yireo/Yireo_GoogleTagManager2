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
     * @return float
     */
    public function format(float $price): float
    {
        return (float)number_format($price, 2, ".", "");
    }
}
