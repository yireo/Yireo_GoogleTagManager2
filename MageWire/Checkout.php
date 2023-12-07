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

    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly BeginCheckout $beginCheckout,
        private readonly AddShippingInfo $addShippingInfo,
        private readonly AddPaymentInfo $addPaymentInfo,
    ) {
    }

    public function triggerBeginCheckout()
    {
        $this->dispatchBrowserEvent('ga:trigger-event', $this->beginCheckout->get());
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
