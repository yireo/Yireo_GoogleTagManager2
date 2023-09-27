<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Cart;

use Magento\Checkout\Model\Cart as CartModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

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
     * @return string
     */
    public function get(): string
    {
        return (string) $this->priceFormatter->format((float)$this->cartModel->getQuote()->getBaseGrandTotal());
    }
}
