<?php declare(strict_types=1);

namespace Tagging\GTM\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Tagging\GTM\Logger\Debugger;
class TriggerCheckoutSessionSaveEvent implements ObserverInterface
{
    private CookieManagerInterface $cookieManager;
    private OrderRepositoryInterface $orderRepository;
    private Debugger $debugger;

    public function __construct(
        CookieManagerInterface $cookieManager,
        OrderRepositoryInterface $orderRepository,
        Debugger $debugger
    ) {
        $this->cookieManager = $cookieManager;
        $this->orderRepository = $orderRepository;
        $this->debugger = $debugger;
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var OrderInterface $order */
            $order = $observer->getData('order');
            $this->debugger->debug("TriggerCheckoutSessionSaveEvent: Processing order " . $order->getIncrementId());

            $marketingCookie = $this->cookieManager->getCookie('trytagging_user_data', 'e30=');
            $this->debugger->debug("TriggerCheckoutSessionSaveEvent: Cookie value: " . $marketingCookie, $marketingCookie);

            $marketingCookie = json_decode(base64_decode($marketingCookie), true);
            $marketingCookie['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
                $_SERVER['HTTP_CLIENT_IP'] ?? 
                $_SERVER['HTTP_X_REAL_IP'] ?? 
                $_SERVER['REMOTE_ADDR'] ?? 
                'unknown';
            $marketingCookie = json_encode($marketingCookie);

            $this->debugger->debug("TriggerCheckoutSessionSaveEvent: Prepared marketing data: " . $marketingCookie, $marketingCookie);

            $extensionAttributes = $order->getExtensionAttributes();
            if (!$extensionAttributes) {
                $this->debugger->debug("TriggerCheckoutSessionSaveEvent: Extension attributes not available on order");
                return;
            }

            $extensionAttributes->setTrytaggingMarketing($marketingCookie);
            $order->setExtensionAttributes($extensionAttributes);

            $this->debugger->debug("TriggerCheckoutSessionSaveEvent: Extension attribute set, about to save order");

            $this->orderRepository->save($order);

            $this->debugger->debug("TriggerCheckoutSessionSaveEvent: Order saved successfully");

            // Verify the save
            $savedOrder = $this->orderRepository->get($order->getId());
            $savedMarketingData = $savedOrder->getExtensionAttributes()->getTrytaggingMarketing();
            $this->debugger->debug("TriggerCheckoutSessionSaveEvent: Verified saved data: " . $savedMarketingData, $savedMarketingData);
        } catch (\Exception $e) {
            $this->debugger->debug("TriggerCheckoutSessionSaveEvent: " . $e->getMessage());
        } catch (\TypeError $e) {
            $this->debugger->debug("TriggerCheckoutSessionSaveEvent (TypeError): " . $e->getMessage());
        }
    }
}
