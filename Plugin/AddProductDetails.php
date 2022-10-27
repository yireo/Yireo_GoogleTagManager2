<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;

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
     * @param string $html
     * @param ProductInterface $product
     * @return string
     */
    public function afterGetProductDetailsHtml(AbstractProduct $abstractProduct, string $html, ProductInterface $product)
    {
        $html .= $this->getProductDetailsBlock()->setProduct($product)->toHtml();
        return $html;
    }

    /**
     * @return BlockInterface
     */
    private function getProductDetailsBlock(): BlockInterface
    {
        return $this->layout->getBlock('yireo_googletagmanager2.product-details');
    }
}
