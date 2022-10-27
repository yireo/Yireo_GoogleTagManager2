<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode;
use Yireo\GoogleTagManager2\DataLayer\Tag\Product\CurrentCategoryName;
use Yireo\GoogleTagManager2\DataLayer\Tag\Product\CurrentPrice;

class ProductDetails implements ArgumentInterface
{
    private CurrentCategoryName $currentCategoryName;
    private CurrencyCode $currencyCode;
    private CurrentPrice $currentPrice;

    /**
     * @param CurrentCategoryName $currentCategoryName
     * @param CurrencyCode $currencyCode
     */
    public function __construct(
        CurrentCategoryName $currentCategoryName,
        CurrencyCode $currencyCode,
        CurrentPrice $currentPrice
    ) {
        $this->currentCategoryName = $currentCategoryName;
        $this->currencyCode = $currencyCode;
        $this->currentPrice = $currentPrice;
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
        return $this->currentPrice->get();
    }
}
