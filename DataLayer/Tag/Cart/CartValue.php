<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Cart;

use Magento\Checkout\Model\Cart as CartModel;
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
     * @return float
     */
    public function get(): float
    {
        return $this->priceFormatter->format((float)$this->cartModel->getQuote()->getSubtotal());
    }
}
