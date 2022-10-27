<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode;
use Yireo\GoogleTagManager2\DataLayer\Tag\Product\CurrentCategoryName;
use Yireo\GoogleTagManager2\DataLayer\Tag\Product\CurrentPrice;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;

/**
 * @property ProductInterface $product
 */
class ProductDetails implements ArgumentInterface
{
    private CurrentCategoryName $currentCategoryName;
    private CurrencyCode $currencyCode;
    private GetCurrentProduct $getCurrentProduct;
    private ?ProductInterface $product = null;

    /**
     * @param CurrentCategoryName $currentCategoryName
     * @param CurrencyCode $currencyCode
     * @param GetCurrentProduct $getCurrentProduct
     */
    public function __construct(
        CurrentCategoryName $currentCategoryName,
        CurrencyCode $currencyCode,
        GetCurrentProduct $getCurrentProduct,
    ) {
        $this->currentCategoryName = $currentCategoryName;
        $this->currencyCode = $currencyCode;
        $this->getCurrentProduct = $getCurrentProduct;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCategoryName(): string
    {
        return $this->currentCategoryName->get();
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode->get();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPrice(): string
    {
        return (string)$this->getProduct()->getFinalPrice();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductName(): string
    {
        return (string)$this->getProduct()->getName();
    }

    /**
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProduct(): ProductInterface
    {
        if ($this->product instanceof ProductInterface) {
            return $this->product;
        }

        return $this->getCurrentProduct->get();
    }

    /**
     * @param ProductInterface $product
     * @return void
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
    }
}
