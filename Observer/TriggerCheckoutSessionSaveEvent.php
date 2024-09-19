<?php declare(strict_types=1);

namespace Tagging\GTM\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Psr\Log\LoggerInterface;

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
            $this->logger->info("TriggerCheckoutSessionSaveEvent: Processing order " . $order->getIncrementId());

            $marketingCookie = $this->cookieManager->getCookie('trytagging_user_data', 'e30=');
            $this->logger->info("TriggerCheckoutSessionSaveEvent: Cookie value: " . $marketingCookie);

            $marketingCookie = json_decode(base64_decode($marketingCookie), true);
            $marketingCookie['ip'] = $_SERVER['REMOTE_ADDR'];
            $marketingCookie = json_encode($marketingCookie);

            $this->logger->info("TriggerCheckoutSessionSaveEvent: Prepared marketing data: " . $marketingCookie);

            $extensionAttributes = $order->getExtensionAttributes();
            if (!$extensionAttributes) {
                $this->logger->error("TriggerCheckoutSessionSaveEvent: Extension attributes not available on order");
                return;
            }

            $extensionAttributes->setTrytaggingMarketing($marketingCookie);
            $order->setExtensionAttributes($extensionAttributes);

            $this->logger->info("TriggerCheckoutSessionSaveEvent: Extension attribute set, about to save order");

            $this->orderRepository->save($order);

            $this->logger->info("TriggerCheckoutSessionSaveEvent: Order saved successfully");

            // Verify the save
            $savedOrder = $this->orderRepository->get($order->getId());
            $savedMarketingData = $savedOrder->getExtensionAttributes()->getTrytaggingMarketing();
            $this->logger->info("TriggerCheckoutSessionSaveEvent: Verified saved data: " . $savedMarketingData);
        } catch (\Exception $e) {
            $this->logger->error("TriggerCheckoutSessionSaveEvent: " . $e->getMessage());
        } catch (\TypeError $e) {
            $this->logger->error("TriggerCheckoutSessionSaveEvent (TypeError): " . $e->getMessage());
        }
    }
}
