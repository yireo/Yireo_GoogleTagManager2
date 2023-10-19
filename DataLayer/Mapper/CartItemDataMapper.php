<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Mapper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config;
use AdPage\GTM\Util\PriceFormatter;

class CartItemDataMapper
{
    private ProductDataMapper $productDataMapper;
    private ProductRepositoryInterface $productRepository;
    private PriceFormatter $priceFormatter;
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ProductDataMapper $productDataMapper
     * @param ProductRepositoryInterface $productRepository
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        ProductDataMapper $productDataMapper,
        ProductRepositoryInterface $productRepository,
        PriceFormatter $priceFormatter,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->productDataMapper = $productDataMapper;
        $this->productRepository = $productRepository;
        $this->priceFormatter = $priceFormatter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param CartItemInterface $cartItem
     * @return array
     * @throws LocalizedException
     */
    public function mapByCartItem(CartItemInterface $cartItem): array
    {
        try {
            $product = $this->productRepository->get($cartItem->getSku());
            $cartItemData = $this->productDataMapper->mapByProduct($product);
        } catch (NoSuchEntityException $e) {
            $cartItemData = [];
        }

        return array_merge($cartItemData, [
            'item_sku' => $cartItem->getSku(),
            'item_name' => $cartItem->getName(),
            'order_item_id' => $cartItem->getItemId(),
            'quantity' => (float) $cartItem->getQty(),
            'price' => $this->getPrice($cartItem)
        ]);
    }

    /**
     * @param CartItemInterface $cartItem
     * @return float
     */
    private function getPrice(CartItemInterface $cartItem): float
    {
        $displayType = (int)$this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
            ScopeInterface::SCOPE_STORE,
            $cartItem->getStoreId() // @phpstan-ignore-line
        );

        switch ($displayType) {
            case Config::DISPLAY_TYPE_EXCLUDING_TAX:
            case Config::DISPLAY_TYPE_BOTH:
                $price = $cartItem->getConvertedPrice(); // @phpstan-ignore-line
                break;
            case Config::DISPLAY_TYPE_INCLUDING_TAX:
            default:
                $price = $cartItem->getPriceInclTax(); // @phpstan-ignore-line
                break;
        }

        return $this->priceFormatter->format((float)$price);
    }
}
