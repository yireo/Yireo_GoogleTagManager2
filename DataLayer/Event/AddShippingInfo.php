<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartItems;

class AddShippingInfo implements EventInterface
{
    private CartItems $cartItems;
    private ShippingMethodManagementInterface $shippingMethodManagement;
    private CheckoutSession $checkoutSession;
    private CartRepositoryInterface $cartRepository;

    /**
     * @param CartItems $cartItems
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CartItems $cartItems,
        ShippingMethodManagementInterface $shippingMethodManagement,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartItems = $cartItems;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        $this->checkoutSession->getQuote()->getShippingAddress()->getShippingMethod();
        if (false === $this->checkoutSession->hasQuote()) {
            return [];
        }

        if (empty($shippingMethod)) {
            $shippingMethod = $this->getShippingMethodFromQuote($this->checkoutSession->getQuote());
        }

        if (empty($shippingMethod)) {
            return [];
        }

        return [
            'event' => 'add_shipping_info',
            'ecommerce' => [
                'shipping_tier' => $shippingMethod,
                'items' => $this->cartItems->get(),
            ],
        ];
    }

    /**
     * @param CartInterface $quote
     * @return string|null
     */
    public function getShippingMethodFromQuote(CartInterface $quote): ?string
    {
        try {
            $shippingMethod = $this->shippingMethodManagement->get($quote->getId());
            if ($shippingMethod instanceof ShippingMethodInterface) {
                return $shippingMethod->getCarrierCode().'_'.$shippingMethod->getMethodCode();
            }
        } catch (NoSuchEntityException $e) {
        } catch (StateException $e) {
        }

        try {
            return $quote->getShippingAddress()->getShippingMethod();
        } catch (NoSuchEntityException $e) {
        }

        return null;
    }
}
