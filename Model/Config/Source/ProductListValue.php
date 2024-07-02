<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ProductListValue implements OptionSourceInterface
{
    public const PRODUCT_FIRST_CATEGORY = 'product_first_category';
    public const CURRENT_CATEGORY = 'current_category';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::PRODUCT_FIRST_CATEGORY,
                'label' => __('Product First Category'),
            ],
            [
                'value' => self::CURRENT_CATEGORY,
                'label' => __('Current Category'),
            ],
        ];
    }
}
