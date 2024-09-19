<?php

declare(strict_types=1);

namespace Tagging\GTM\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Psr\Log\LoggerInterface;
use TypeError;

class TriggerCheckoutSessionSaveEvent implements ObserverInterface
{
    private LoggerInterface $logger;
    private CookieManagerInterface $cookieManager;
    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        LoggerInterface $logger,
        CookieManagerInterface $cookieManager,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->logger = $logger;
        $this->cookieManager = $cookieManager;
        $this->orderRepository = $orderRepository;
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var OrderInterface $order */
            $order = $observer->getData('order');
            $marketingCookie = $this->cookieManager->getCookie('trytagging_user_data', 'e30=');
            $marketingCookie = json_decode(base64_decode($marketingCookie), true);
            $marketingCookie['ip'] = $_SERVER['REMOTE_ADDR'];
            $marketingCookie = json_encode($marketingCookie);
 
            $order->getExtensionAttributes()->setTrytaggingMarketing($marketingCookie);
            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            $this->logger->error("TriggerCheckoutSessionSaveEvent: " . $e->getMessage());
        } catch (TypeError $e) {
            $this->logger->error("TriggerCheckoutSessionSaveEvent: TypeError");
        }
    }
}
