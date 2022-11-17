<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\SessionDataProvider;

use Magento\Checkout\Model\Session as CheckoutSession;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\Logger\Debugger;

class CheckoutSessionDataProvider implements CheckoutSessionDataProviderInterface
{
    private CheckoutSession $checkoutSession;
    private Debugger $debugger;

    public function __construct(
        CheckoutSession $checkoutSession,
        Debugger $debugger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->debugger = $debugger;
    }

    public function add(string $identifier, array $data)
    {
        $gtmData = $this->get();
        $gtmData[$identifier] = $data;
        $this->debugger->debug('CheckoutSessionDataProvider::add(): '.$identifier, $data);
        $this->checkoutSession->setYireoGtmData($gtmData);
    }

    public function get(): array
    {
        $gtmData = $this->checkoutSession->getYireoGtmData();
        if (is_array($gtmData)) {
            return $gtmData;
        }

        return [];
    }

    public function clear()
    {
        $this->checkoutSession->setYireoGtmData([]);
    }
}
