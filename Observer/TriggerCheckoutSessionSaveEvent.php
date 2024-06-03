<?php

declare(strict_types=1);

namespace Tagging\GTM\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use TypeError;

class TriggerCheckoutSessionSaveEvent implements ObserverInterface
{
    private LoggerInterface $logger;
    private CheckoutSession $checkoutSession;
    private CookieManagerInterface $cookieManager;
    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        LoggerInterface $logger,
        CheckoutSession $checkoutSession,
        CookieManagerInterface $cookieManager,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->logger = $logger;
        $this->cookieManager = $cookieManager;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var OrderInterface $order */
            $order = $observer->getData('order');
            $customData = $this->checkoutSession->getData('trytagging_marketing');
            $marketingCookie = $this->cookieManager->getCookie('trytagging_user_data');

            if ($marketingCookie) {
                $marketingCookie = json_decode(base64_decode($marketingCookie), true);
                $marketingCookie['ip'] = $_SERVER['REMOTE_ADDR'];
                $marketingCookie = json_encode($marketingCookie);
            }


            $saveData = strlen($marketingCookie) > 10 ? $marketingCookie : $customData;

            if ($saveData && strlen($saveData) > 10) {
                $order->setData('trytagging_marketing', $saveData);
                $this->orderRepository->save($order);
                $this->checkoutSession->unsetData('trytagging_marketing');
            }
        } catch (\Exception $e) {
            $this->logger->error("TriggerCheckoutSessionSaveEvent: " . $e->getMessage());
        } catch (TypeError $e) {
            $this->logger->error("TriggerCheckoutSessionSaveEvent: TypeError");
        }
    }
}
