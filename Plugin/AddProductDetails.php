<?php declare(strict_types=1);

namespace AdPage\GTM\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use AdPage\GTM\Exception\BlockNotFound;

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

        $html .= $block->setProduct($product)->toHtml(); // @phpstan-ignore-line
        return $html;
    }

    /**
     * @return BlockInterface
     * @throws BlockNotFound
     */
    private function getProductDetailsBlock(): BlockInterface
    {
        $block = $this->layout->getBlock('AdPage_GTM.product-details');
        if ($block instanceof BlockInterface) {
            return $block;
        }

        throw new BlockNotFound('Block "AdPage_GTM.product-details" not found');
    }
}
