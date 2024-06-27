<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\MageWire;

use Magento\Checkout\Model\Session as CheckoutSession;
use Yireo\GoogleTagManager2\DataLayer\Event\AddPaymentInfo;
use Yireo\GoogleTagManager2\DataLayer\Event\AddShippingInfo;
use Yireo\GoogleTagManager2\DataLayer\Event\BeginCheckout;

class Checkout extends Component
{
    protected $listeners = [
        'shipping_method_selected' => 'triggerShippingMethod',
        'payment_method_selected' => 'triggerPaymentMethod',
    ];
    private CheckoutSession $checkoutSession;
    private AddShippingInfo $addShippingInfo;
    private AddPaymentInfo $addPaymentInfo;

    public function __construct(
        CheckoutSession $checkoutSession,
        AddShippingInfo $addShippingInfo,
        AddPaymentInfo $addPaymentInfo
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->addShippingInfo = $addShippingInfo;
        $this->addPaymentInfo = $addPaymentInfo;
    }

    public function triggerShippingMethod()
    {
        $this->dispatchBrowserEvent('ga:trigger-event', $this->addShippingInfo->get());
    }

    public function triggerPaymentMethod()
    {
        $this->addPaymentInfo->setCartId((int) $this->checkoutSession->getQuote()->getId());
        $this->addPaymentInfo->setPaymentMethod((string) $this->checkoutSession->getQuote()->getPayment()->getMethod());
        $this->dispatchBrowserEvent('ga:trigger-event', $this->addPaymentInfo->get());
    }
}
