<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag\Cart;

use Magento\Checkout\Model\Cart as CartModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use AdPage\GTM\Api\Data\TagInterface;
use AdPage\GTM\Util\PriceFormatter;

class CartValue implements TagInterface
{
    private CartModel $cartModel;
    private PriceFormatter $priceFormatter;

    /**
     * @param CartModel $cartModel
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        CartModel $cartModel,
        PriceFormatter $priceFormatter
    ) {
        $this->cartModel = $cartModel;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * @return float
     */
    public function get(): float
    {
        return $this->priceFormatter->format((float)$this->cartModel->getQuote()->getBaseGrandTotal());
    }
}
