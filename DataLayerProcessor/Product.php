<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayerProcessor;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\GetCategoryFromProduct;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class Product implements ProcessorInterface
{
    private PriceFormatter $priceFormatter;
    private GetCurrentProduct $getCurrentProduct;
    private GetCategoryFromProduct $getCategoryFromProduct;

    /**
     * ProductProvider constructor.
     *
     * @param PriceFormatter $priceFormatter
     * @param GetCurrentProduct $getCurrentProduct
     * @param GetCategoryFromProduct $getCategoryFromProduct
     */
    public function __construct(
        PriceFormatter $priceFormatter,
        GetCurrentProduct $getCurrentProduct,
        GetCategoryFromProduct $getCategoryFromProduct
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->getCurrentProduct = $getCurrentProduct;
        $this->getCategoryFromProduct = $getCategoryFromProduct;
    }

    /**
     * @inheritDoc
     */
    public function process(array $data): array
    {
        /** @var ProductInterface $product */
        $product = $this->getCurrentProduct->get();
        if (!$product) {
            return [];
        }

        $data = [
            'ecommerce' => [
                'value' => $this->priceFormatter->format((float)$product->getPrice()),
                'detail' => [
                    'actionField' => [
                        'action' => 'detail'
                    ],
                    'products' => [
                        [
                            'id' => $product->getId(),
                            'name' => $product->getName(),
                            'sku' => $product->getSku(),
                            'price' => $product->getPrice(),
                        ]

                    ]
                ]
            ],
            'event' => 'productDetailImpression'
        ];

        $category = $this->getCategoryFromProduct->get($product);
        $data['ecommerce']['detail']['actionField']['list'] = $category->getName();

        $dynamicRemarketing = [
            'google_tag_params' => [
                'ecomm_prodid' => $product->getId(),
                'ecomm_pname' => $product->getName(),
                'ecomm_pvalue' => $this->priceFormatter->format((float)$product->getPrice()),
                'ecomm_totalvalue' => $this->priceFormatter->format((float)$product->getPrice()),
                'ecomm_category' => $category->getName(),
            ],
        ];

        return array_replace_recursive($data, $dynamicRemarketing);
    }
}
