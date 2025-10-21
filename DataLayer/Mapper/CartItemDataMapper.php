<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as CartItem;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config;
use Yireo\GoogleTagManager2\Util\PriceFormatter;
use Yireo\GoogleTagManager2\Util\ProductProvider;

class CartItemDataMapper
{
    private ProductDataMapper $productDataMapper;
    private ProductProvider $productProvider;
    private PriceFormatter $priceFormatter;
    private ScopeConfigInterface $scopeConfig;
    private bool $useProductProvider = false;

    /**
     * @param ProductDataMapper $productDataMapper
     * @param ProductProvider $productProvider
     * @param PriceFormatter $priceFormatter
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ProductDataMapper $productDataMapper,
        ProductProvider $productProvider,
        PriceFormatter $priceFormatter,
        ScopeConfigInterface $scopeConfig,
        bool $useProductProvider = false
    ) {
        $this->productDataMapper = $productDataMapper;
        $this->productProvider = $productProvider;
        $this->priceFormatter = $priceFormatter;
        $this->scopeConfig = $scopeConfig;
        $this->useProductProvider = $useProductProvider;
    }

    /**
     * @param CartItem $cartItem
     * @return array
     * @throws LocalizedException
     */
    public function mapByCartItem(CartItem $cartItem): array
    {
        try {
            if ($this->useProductProvider) {
                $product = $this->productProvider->getBySku($cartItem->getSku());
            } else {
                $product = $cartItem->getProduct();
            }

            $cartItemData = $this->productDataMapper->mapByProduct($product);
        } catch (NoSuchEntityException $e) {
            $cartItemData = [];
        }

        $price = $this->getPrice($cartItem);
        return array_merge($cartItemData, [
            'item_sku' => $cartItem->getSku(),
            'item_name' => $cartItem->getName(),
            'order_item_id' => $cartItem->getItemId(),
            'quantity' => (float) $cartItem->getQty(),
            'price_excl_tax' => $cartItem->getRowTotal() / $cartItem->getQty(),
            'price_incl_tax' => $cartItem->getRowTotalInclTax() / $cartItem->getQty(),
            'price' => $price,
            'discount' => $this->getPriceDiscount($cartItem, $price)
        ]);
    }

    /**
     * @param CartItem $cartItem
     * @return float
     */
    private function getPrice(CartItem $cartItem): float
    {
        $displayType = (int)$this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
            ScopeInterface::SCOPE_STORE,
            $cartItem->getStoreId()
        );

        switch ($displayType) {
            case Config::DISPLAY_TYPE_EXCLUDING_TAX:
            case Config::DISPLAY_TYPE_BOTH:
                $price = ($cartItem->getRowTotal() - $cartItem->getDiscountAmount()) / $cartItem->getQty();
                break;
            case Config::DISPLAY_TYPE_INCLUDING_TAX:
            default:
                $price = $cartItem->getRowTotalInclTax() / $cartItem->getQty();
                break;
        }

        return $this->priceFormatter->format((float)$price);
    }

    private function getPriceDiscount(CartItem $cartItem, float $price): float
    {
        return $cartItem->getDiscountAmount() / $cartItem->getQty();
    }
}
