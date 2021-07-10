<?php declare(strict_types=1);
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Yireo\GoogleTagManager2\Api\AttributesViewModelInterface;
use Yireo\GoogleTagManager2\Api\ProductViewModelInterface;

/**
 * Class \Yireo\GoogleTagManager2\ViewModel\Product
 */
class Product implements ArgumentInterface,ProductViewModelInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var AttributesViewModelInterface
     */
    private $attributesViewModel;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Product constructor.
     * @param RequestInterface $request
     * @param AttributesViewModelInterface $attributesViewModel
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        RequestInterface $request,
        AttributesViewModelInterface $attributesViewModel,
        ProductRepositoryInterface $productRepository
    ){
        $this->request = $request;
        $this->attributesViewModel = $attributesViewModel;
        $this->productRepository = $productRepository;
    }

    /**
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getCurrentProduct(): ProductInterface
    {
        return $this->productRepository->getById((int)$this->request->getParam('id'));
    }

    /**
     * @param ProductInterface $product
     * @return void
     */
    public function addProduct(ProductInterface $product)
    {
        $attributes = $this->mapProductAttributes($product);
        foreach ($attributes as $attributeName => $attributeValue) {
            $this->attributesViewModel->addAttribute($attributeName, $attributeValue);
        }
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function mapProductAttributes(ProductInterface $product): array
    {
        return [
            'productId' => $product->getId(),
            'productName' => $product->getName(),
            'productSku' => $product->getSku(),
            'productPrice' => $this->getProductPrice($product)
        ];
    }

    /**
     * @param ProductInterface $product
     * @return float
     */
    public function getProductPrice(ProductInterface $product): float
    {
        return $product->getFinalPrice();
    }
}
