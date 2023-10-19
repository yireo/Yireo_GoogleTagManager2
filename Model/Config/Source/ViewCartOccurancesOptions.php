<?php declare(strict_types=1);

namespace AdPage\GTM\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ViewCartOccurancesOptions implements OptionSourceInterface
{
    const EVERYWHERE = 'everywhere';
    const CART_PAGE_ONLY = 'cart_page';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        $options = [
            ['value' => self::CART_PAGE_ONLY, 'label' => __('Cart-page only')],
            ['value' => self::EVERYWHERE, 'label' => __('Everywhere (including minicart)')],
        ];

        return $options;
    }
}
