<?php declare(strict_types=1);

namespace Tagging\GTM\Util;

/**
 * Class PriceFormatter
 *
 * @package Tagging\GTM\Util
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
