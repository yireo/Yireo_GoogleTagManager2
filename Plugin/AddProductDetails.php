<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Yireo\GoogleTagManager2\Exception\BlockNotFound;

class AddProductDetails
{
    private LayoutInterface $layout;

    public function __construct(
        LayoutInterface $layout
    ) {
        $this->layout = $layout;
    }

    /**
     * @param AbstractProduct $abstractProduct
     * @param mixed $html
     * @param ProductInterface $product
     * @return string
     */
    public function afterGetProductDetailsHtml(AbstractProduct $abstractProduct, $html, ProductInterface $product)
    {
        try {
            $block = $this->getProductDetailsBlock();
        } catch (BlockNotFound $blockNotFound) {
            return $html;
        }

        $html .= $block->setData('product', $product)->toHtml();
        return $html;
    }

    /**
     * @return AbstractBlock
     * @throws BlockNotFound
     */
    private function getProductDetailsBlock(): AbstractBlock
    {
        $block = $this->layout->getBlock('yireo_googletagmanager2.product-details');
        if ($block instanceof AbstractBlock) {
            return $block;
        }

        throw new BlockNotFound('Block "yireo_googletagmanager2.product-details" not found');
    }
}
