<?php declare(strict_types=1);

namespace AdPage\GTM\Util;

/**
 * Class PriceFormatter
 *
 * @package AdPage\GTM\Util
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
