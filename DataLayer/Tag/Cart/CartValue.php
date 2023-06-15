<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Cart;

use Magento\Checkout\Model\Cart as CartModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;

class CartValue implements TagInterface
{
    private CartModel $cartModel;

    /**
     * @param CartModel $cartModel
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function __construct(
        CartModel $cartModel
    ) {
        $this->cartModel = $cartModel;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function get(): string
    {
        return number_format($this->cartModel->getQuote()->getBaseGrandTotal(), 4, '.');
    }
}
