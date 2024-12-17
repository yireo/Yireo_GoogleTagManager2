<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\MageWire;

use Magento\Checkout\Model\Session as CheckoutSession;
use Yireo\GoogleTagManager2\DataLayer\Event\AddPaymentInfo;
use Yireo\GoogleTagManager2\DataLayer\Event\AddShippingInfo;

class Checkout extends Component
{
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

    public function boot(): void
    {
        // @phpstan-ignore-next-line
        parent::boot();

        // @todo: Do this only with the Hyva Checkout
        // @phpstan-ignore-next-line
        $this->listeners['shipping_method_selected'] = 'triggerShippingMethod';
        $this->listeners['payment_method_selected'] = 'triggerPaymentMethod';

        // @todo: Do this only with the Loki Checkout
        $this->listeners['afterSaveShippingMethod'] = 'triggerShippingMethod';
        $this->listeners['afterSavePaymentMethod'] = 'triggerPaymentMethod';
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
